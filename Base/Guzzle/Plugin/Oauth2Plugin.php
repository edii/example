<?php
namespace Araneum\Base\Guzzle\Plugin;

use Guzzle\Common\Event;
use Guzzle\Common\Collection;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * OAuth2 signing plugin
 *
 * @package Araneum\Base\Guzzle\Plugin
 */
class Oauth2Plugin implements EventSubscriberInterface
{
    /**
     * Token place constants.
     */
    const TOKEN_HEADER = 'header';
    const TOKEN_QUERY  = 'query';

    /**
     * @var Collection Configuration settings
     */
    protected $config;

    /**
     * Create a new OAuth 2.0 plugin
     *
     * @param array $config Configuration array containing these parameters:
     *                      - string 'token_url'       Url for getting access token
     *                      - string 'token_place'
     *                      - string 'client_id'
     *                      - string 'client_secret'
     *                      - string 'grant_type'     Type of access. By default, for bearer it is "client_credentials"
     *                      - string 'scope'
     */
    public function __construct($config)
    {
        $this->config = Collection::fromConfig(
            $config,
            [
                'client_id' => 'anonymous',
                'client_secret' => 'anonymous',
                'grant_type' => 'client_credentials',
                'scope' => '',
                'token_place' => self::TOKEN_HEADER,
            ],
            [
                'token_url',
                'client_id',
                'client_secret',
            ]
        );
    }

    /**
     * Get list of subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'request.before_send' => [
                'onRequestBeforeSend',
                -1000,
            ],
        ];
    }

    /**
     * Request before-send event handler
     *
     * @param Event $event Event received
     * @return string
     */
    public function onRequestBeforeSend(Event $event)
    {
        $request = $event['request'];
        $accessToken = $this->getAccessToken();

        /** @var Request $request */

        if ($this->config['token_place'] == self::TOKEN_HEADER) {
            $request->setHeader(
                'Authorization',
                'Bearer '.$accessToken
            );
        }

        if ($this->config['token_place'] == self::TOKEN_QUERY) {
            $request->getQuery()->add('access_token', $accessToken);
        }

        return $accessToken;
    }

    /**
     * Get access token from api
     *
     * @return string
     */
    private function getAccessToken()
    {
        $http = new Client();
        $request = $http->post(
            $this->config['token_url'],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            $this->getTokenRequestParams()
        );

        $response = $request->send();
        $responseBody = $response->getBody(true);
        $responseArray = json_decode($responseBody, true);

        $accessToken = null;
        switch (true) {
            case $responseArray != null:
                if (isset($responseArray['access_token'])) {
                    $accessToken = $responseArray['access_token'];
                }
                break;
            case preg_match('/access_token=([^\&\,]*)/', $responseBody, $matches):
                if (!empty($matches[1])) {
                    $accessToken = $matches[1];
                }
                break;
        }

        if (empty($accessToken)) {
            throw new BadRequestHttpException('Access token not received: '.$responseBody);
        }

        return $accessToken;
    }

    /**
     * Generate parameters to get access token from api
     *
     * @return array
     */
    private function getTokenRequestParams()
    {
        $parameters = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => $this->config['grant_type'],
        ];

        if (!empty($this->config['refresh_token'])) {
            $parameters['refresh_token'] = $this->config['refresh_token'];
        }

        return $parameters;
    }
}
