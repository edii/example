<?php

namespace Araneum\Base\Service\ApiSenders;

use Araneum\Base\Service\AbstractApiSender;
use Araneum\Bundle\AgentBundle\Entity;
use Doctrine\ORM\EntityManager;
use Guzzle\Http\Message\Response;
use Araneum\Bundle\AgentBundle\Entity\SenderLog;
use Guzzle\Service\ClientInterface;

/**
 * Class SpotApiSenderService
 *
 * @package Araneum\Base\Service\Guzzle
 */
class SpotApiSenderService extends AbstractApiSender
{
    /**
     * @var array
     */
    protected $logDisableFields = ['password'];
    /**
     * @var string
     */
    protected $spotApiPublicUrlLogin;

    /**
     * SpotApiSenderService constructor.
     *
     * @param ClientInterface $guzzle
     * @param EntityManager   $em
     * @param bool            $enableJsonResponse
     * @param string          $spotApiPublicUrlLogin
     */
    public function __construct(ClientInterface $guzzle, EntityManager $em, $enableJsonResponse, $spotApiPublicUrlLogin)
    {
        parent::__construct($guzzle, $em, $enableJsonResponse);
        $this->spotApiPublicUrlLogin = $spotApiPublicUrlLogin;
    }

    /**
     * Send request to spot public api url
     *
     * @param string $method           HTTP method. Defaults to GET
     * @param string $spotApiPublicUrl Base url to send to
     * @param array  $headers
     * @param array  $requestData
     * @param array  $log
     * @return \Guzzle\Http\Message\Response
     */
    public function sendToPublicUrl($method, $spotApiPublicUrl, array $headers, array $requestData, array $log = [])
    {
        $log['request'] = $requestData;
        try {
            if (!filter_var($spotApiPublicUrl, FILTER_VALIDATE_URL)) {
                throw new \BadMethodCallException("Not valid spot public utl: ".$spotApiPublicUrl);
            }

            $this->guzzle->setBaseUrl($spotApiPublicUrl);
            $response = $this->guzzle->createRequest($method, $this->spotApiPublicUrlLogin, $headers, $requestData)
                ->send();
            if (!empty($response)) {
                $log['response'] = $response->getBody(true);
            }
            $this->createSenderLog($log, SenderLog::TYPE_OK);

            return $response;
        } catch (\BadMethodCallException $e) {
            $log['response'] = $e->getMessage();
            $this->createSenderLog($log, SenderLog::TYPE_BAD_METHOD_CALL);

            return $e;
        } catch (\Exception $e) {
            $log['response'] = $e->getCode().' : '.$e->getMessage();
            $this->createSenderLog($log, SenderLog::TYPE_OTHER_EXCEPTION);

            return $e;
        }
    }

    /**
     * Get data from spotoption
     *
     * @param array $requestData
     * @param array $spotCredential
     * @return array
     */
    public function get(array $requestData, array $spotCredential)
    {
        $response = $this->send($requestData, $spotCredential);
        $response = $response->json();

        if (isset($response['status']['connection_status']) &&
            $response['status']['connection_status'] === 'successful' &&
            $response['status']['operation_status'] === 'successful'
        ) {
            return $response['status'][$requestData['MODULE']];
        } else {
            return $response['status']['errors']['error'];
        }
    }

    /**
     * @param array $requestData
     * @param array $credentials
     * @return bool
     */
    public function senderLogic($requestData, $credentials)
    {
        $spotCredential = $credentials['credentials'];
        if (!$this->isSpotCredentialValid($spotCredential)) {
            $error = "Check spot credential data, some value invalid: ".print_r($spotCredential, true);
            throw new \BadMethodCallException($error);
        }

        $this->guzzle->setBaseUrl($spotCredential['apiPrivateUrl']);
        $body = array_merge(
            [
                'api_username' => $spotCredential['apiUser'],
                'api_password' => $spotCredential['apiPassword'],
                'jsonResponse' => $this->enableJsonResponse ? 'true' : 'false',
            ],
            $requestData
        );

        return $this->guzzle->post(null, null, $body)->send();
    }

    /**
     * Get errors from response or null if no errors
     *
     * @param Response $response
     * @return string|null
     */
    public function getErrors($response)
    {
        if ($response instanceof \Exception) {
            return $response->getMessage();
        }

        $decodedResponse = $response->json();
        if (!array_key_exists('status', $decodedResponse)) {
            throw new \BadMethodCallException('Unsupported response format '.print_r($decodedResponse, true));
        }

        $status = $decodedResponse['status'];
        if (array_key_exists('connection_status', $status) &&
            $status['connection_status'] === 'successful' &&
            array_key_exists('operation_status', $status) &&
            $status['operation_status'] === 'successful'
        ) {
            return null;
        }

        return json_encode($status['errors']);
    }

    /**
     * Get Errors from public url
     *
     * @param Response $response
     * @return string|null
     */
    public function getErrorsFromPublic(Response $response)
    {
        $decodedResponse = $response->json();
        if (!array_key_exists('status', $decodedResponse)) {
            throw new \BadMethodCallException('Unsupported response format '.print_r($decodedResponse, true));
        }

        if ($decodedResponse['status'] === true) {
            return null;
        }

        return json_encode($decodedResponse['errors']);
    }

    /**
     * Generate spot session
     *
     * @param int $length
     * @return string
     */
    public function generateSpotSession($length = 34)
    {
        return substr(uniqid(sha1(time()), true), 0, $length);
    }

    /**
     * Validate spot credential
     *
     * @param array $spotCredential
     * @return bool
     */
    private function isSpotCredentialValid($spotCredential)
    {
        return
            array_key_exists('apiPrivateUrl', $spotCredential) &&
            array_key_exists('apiUser', $spotCredential) &&
            array_key_exists('apiPassword', $spotCredential) &&
            filter_var($spotCredential['apiPrivateUrl'], FILTER_VALIDATE_URL) &&
            $spotCredential['apiUser'] !== null &&
            $spotCredential['apiPassword'] !== null;
    }
}
