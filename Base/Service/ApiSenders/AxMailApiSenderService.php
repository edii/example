<?php

namespace Araneum\Base\Service\ApiSenders;

use Araneum\Base\Service\AbstractApiSender;
use Araneum\Bundle\AgentBundle\Entity;
use Guzzle\Http\Message\RequestInterface;

/**
 * Class AxMailApiSenderService
 *
 * @package Araneum\Base\Service\Guzzle
 */
class AxMailApiSenderService extends AbstractApiSender
{
    /**
     * @param array $requestData
     * @param array $credendials
     * @return bool
     */
    public function senderLogic($requestData, $credendials)
    {
        $axMailCredential = $credendials['credentials'];
        $url = $axMailCredential['apiPrivateUrl'];
        $requestData['secret'] = $axMailCredential['apiPassword'];
        $requestData['brand'] = $axMailCredential['apiUser'];
        $url .= '?'.http_build_query($requestData);
        $this->guzzle->setBaseUrl($url);

        return $this->guzzle->get()->send();
    }
}
