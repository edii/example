<?php

namespace Araneum\Base\Service\ApiSenders;

use Araneum\Base\Service\AbstractApiSender;
use Araneum\Bundle\AgentBundle\Entity;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Oauth\OauthPlugin;

/**
 * Class AweberApiSenderService
 *
 * @package Araneum\Base\Service\Guzzle
 */
class AweberApiSenderService extends AbstractApiSender
{
    const METHODS = [
        'GET',
        'POST',
        'PATCH',
        'DELETE',
    ];

    protected $url;

    /**
     * @param array $requestData
     * @param array $credentials
     * @return Response
     */
    public function senderLogic($requestData, $credentials)
    {
        $aweberCredentials = $credentials['credentials'];
        $client = new Client($aweberCredentials['apiPrivateUrl']);
        $this->generateUrlByRequestData($requestData);
        $oauth = new OauthPlugin($aweberCredentials['otherCredential']['OAUth']);
        $client->addSubscriber($oauth);
        $method = strtolower($requestData['METHOD']);
        unset($requestData['METHOD']);

        if (!empty($requestData['ws_op'])) {
            $requestData['ws.op'] = $requestData['ws_op'];
            unset($requestData['ws_op']);
        }

        if ($method === "patch") {
            $customFields = '';
            if (!empty($requestData['custom_fields'])) {
                $customFields = ', "custom_fields": '.$requestData['custom_fields'];
                unset($requestData['custom_fields']);
            }

            $encodedString = json_encode($requestData);

            return $client
                ->patch($this->url)
                ->setBody(
                    substr($encodedString, 0, -1).$customFields.'}',
                    'application/json'
                )
                ->send();
        }

        $this->url .= '?'.http_build_query($requestData);

        if ($method === 'get') {
            return $client->get($this->url, null, $requestData)->send();
        } else {
            $response = $client
                ->{$method}(
                    $this->url
                )
                ->setBody(http_build_query($requestData), 'application/x-www-form-urlencoded')
                ->send();

            if ($response->getStatusCode() == 201) {
                $response->setBody("Success");
            }

            return $response;
        }
    }

    /**
     * @param array $requestData
     * @return string
     */
    public function generateUrlByRequestData(&$requestData)
    {
        $this->url = '';
        foreach ($requestData as $key => $value) {
            $subkey = strtoupper($value);

            if ('MODULE_'.$subkey == $key) {
                $this->url .= $value.'/';
                unset($requestData['MODULE_'.$subkey]);

                if (!empty($requestData['ID_'.$subkey])) {
                    $this->url .= $requestData['ID_'.$subkey].'/';
                    unset($requestData['ID_'.$subkey]);
                }
            }
        }
        $this->url = substr_replace($this->url, '', -1);
    }
}
