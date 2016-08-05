<?php

namespace Araneum\Base\Service\ApiSenders;

use Araneum\Base\Guzzle\Plugin\Oauth2Plugin;
use Araneum\Base\Service\AbstractApiSender;
use Araneum\Bundle\AgentBundle\Entity;
use Guzzle\Http\Message\Response;

/**
 * Class TransactApiSenderService
 *
 * @package Araneum\Base\Service\ApiSenders
 */
class TransactApiSenderService extends AbstractApiSender
{
    /**
     * @param array $requestData
     * @param array $credentials
     * @return Response
     */
    public function senderLogic($requestData, $credentials)
    {
        $credentials = $credentials['credentials'];

        $tokenUrl = $credentials['otherCredential']['token_url'];

        $newConfigArray = [
            'token_url' => $tokenUrl,
            'client_id' => $credentials['otherCredential']['client_id'],
            'client_secret' => $credentials['otherCredential']['client_secret'],
            'refresh_token' => $credentials['otherCredential']['refresh_token'],
            'grant_type' => $credentials['otherCredential']['grant_type'],
        ];

        $plugin = new Oauth2Plugin($newConfigArray);

        $this->guzzle->addSubscriber($plugin);
        $this->guzzle->setBaseUrl($credentials['apiPrivateUrl']);

        return $this->guzzle->post(null, ['Content-Type' => 'text/xml'], $requestData['body'])->send();
    }
}
