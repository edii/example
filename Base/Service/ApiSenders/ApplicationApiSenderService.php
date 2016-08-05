<?php

namespace Araneum\Base\Service\ApiSenders;

use Araneum\Base\Service\AbstractApiSender;
use Guzzle\Service\ClientInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Class ApplicationApiSenderService
 *
 * @package Araneum\Base\Service\Application
 */
class ApplicationApiSenderService extends AbstractApiSender
{
    const SET_SPOT_USER_DATA = "SET_SPOT_USER_DATA";

    /**
     * @var array
     */
    protected $applicationApiConfig;

    /**
     * ApplicationApiSenderService constructor.
     *
     * @param ClientInterface $guzzle
     * @param EntityManager   $em
     * @param bool            $enableJsonResponse
     * @param array           $applicationApiConfig
     */
    public function __construct(
        ClientInterface $guzzle,
        EntityManager $em,
        $enableJsonResponse,
        $applicationApiConfig
    ) {
        parent::__construct($guzzle, $em, $enableJsonResponse);
        $this->applicationApiConfig = $applicationApiConfig;
    }

    /**
     * Send request to application
     *
     * @param  array $requestData
     * @param  array $credential
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function senderLogic($requestData, $credential)
    {
        $parameters = $credential['credentials'];

        $requestConfig = $this->applicationApiConfig[$parameters['requestConfig']];

        $url = $this->fillUrl($requestConfig['url'], $parameters['urlParams']);

        $this->guzzle->setBaseUrl($parameters['baseUrl']);

        return $this->guzzle->createRequest(
            $requestConfig['method'],
            $url,
            $parameters['headers']?:[],
            $requestData,
            $parameters['params']?:[]
        );
    }


    /**
     * @param string $relativeUrl
     * @param array  $data
     * @return string
     */
    private function fillUrl($relativeUrl, array $data)
    {
        if (preg_match('/\{[^\}]*\{|\}[^\{]*\}|\{[^\}]*$/', $relativeUrl)) {
            throw new InvalidParameterException(
                'Parameters in URL pattern should start from symbol <{> and ends with <}>. Example: {accauntId}'
            );
        }

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $relativeUrl = str_replace('{'.$key.'}', $value, $relativeUrl);
            }
        }

        return $relativeUrl;
    }
}
