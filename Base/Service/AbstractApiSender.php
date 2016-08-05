<?php

namespace Araneum\Base\Service;

use Guzzle\Http\Message\Response;
use Guzzle\Service\ClientInterface;
use Doctrine\ORM\EntityManager;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Exception\CurlException;
use Araneum\Bundle\AgentBundle\Entity\SenderLog;

/**
 * Abstract class AbstractApiSender
 *
 * @package Araneum\Base\Service
 */
abstract class AbstractApiSender
{
    protected $logDisableFields = [];
    /**
     * @var ClientInterface
     */
    protected $guzzle;
    protected $enableJsonResponse;

    /**
     * Abstract sender constructor.
     *
     * @param ClientInterface $guzzle
     * @param EntityManager   $em
     * @param boolean         $enableJsonResponse
     */
    public function __construct(
        ClientInterface $guzzle,
        EntityManager $em,
        $enableJsonResponse
    ) {
        $this->guzzle = $guzzle;
        $this->em = $em;
        $this->enableJsonResponse = $enableJsonResponse;
    }

    /**
     * Send request to core
     *
     * @param array $data
     * @param mixed $credential
     * @return \Guzzle\Http\Message\Response
     */
    public function send(array $data, array $credential)
    {
        $credential = [
            'credentials' => (array) $credential['credentials'],
            'log' => (array) $credential['log'],
        ];
        $log = $credential['log'] ? $credential['log'] : [];
        $log['request'] = $data;
        try {
            $response = $this->senderLogic($data, $credential);
            if (!empty($response)) {
                $log['response'] = $response->getBody(true);
            }
            $this->createSenderLog($log, SenderLog::TYPE_OK);

            return $response;
        } catch (\BadMethodCallException $e) {
            $log['response'] = $e->getMessage();
            $this->createSenderLog($log, SenderLog::TYPE_BAD_METHOD_CALL);

            return $e;
        } catch (CurlException $e) {
            $log['response'] = $e->getError();
            $this->createSenderLog($log, SenderLog::TYPE_CURL);

            return $e;
        } catch (RequestException $e) {
            $code = $e->getRequest()->getResponse()->getStatusCode();
            $message = $e->getRequest()->getResponse()->getBody(true);
            $log['response'] = $code.' : '.$message;
            $this->createSenderLog($log, SenderLog::TYPE_REQUEST);

            return $e;
        } catch (\Exception $e) {
            $log['response'] = $e->getCode().' : '.$e->getMessage();
            $this->createSenderLog($log, SenderLog::TYPE_OTHER_EXCEPTION);

            return $e;
        }
    }

    /**
     * @param array $data
     * @param array $credential
     * @return Response $response
     */
    abstract public function senderLogic($data, $credential);

    /**
     * Create and save sender log
     *
     * @param array $log
     * @param int   $status
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createSenderLog(array $log, $status)
    {
        $fields = array_keys($log['request']);
        for ($i = 0; $i < count($fields) - 1; $i++) {
            if (in_array(strtolower($fields[$i]), $this->logDisableFields)) {
                $log['request'][$fields[$i]] = "***********";
            }
        }

        $log['request'] = json_encode($log['request']);
        $log['response'] = json_encode($log['response']);

        $entityLog = (new SenderLog())
            ->setStatus($status)
            ->setRequest($log['request'])
            ->setResponse($log['response'])
            ->setApplication($this->em->getReference('AraneumMainBundle:Application', $log['applicationId']))
            ->setAgent($this->em->getReference('AraneumAgentBundle:Agent', $log['agentId']));

        $this->em->persist($entityLog);
        $this->em->flush();
    }
}
