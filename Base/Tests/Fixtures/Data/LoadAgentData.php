<?php

namespace Araneum\Base\Tests\Fixtures\Data;

use Araneum\Bundle\AgentBundle\Entity\Agent;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadAgentData
 *
 * @package Araneum\Base\Tests\Fixtures\Data
 */
class LoadAgentData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private $container;

    const APIES_CREDENTIALS = [
        'AxMail' => [
            'Ultratrade' => [
                'apiPrivateUrl' => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiPublicUrl'  => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiUser'       => 'ultratrade',
                'apiPassword'   => 'da55a3d5f8723d5f342b49abf34edb9c',
            ],
            'toroption' => [
                'apiPrivateUrl' => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiPublicUrl'  => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiUser'       => 'toroption',
                'apiPassword'   => 'da55a3d5f8723d5f342b49abf34edb9c',
            ],
            'ixoption' => [
                'apiPrivateUrl' => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiPublicUrl'  => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiUser'       => 'ixoption',
                'apiPassword'   => 'da55a3d5f8723d5f342b49abf34edb9c',
            ],
            'Tradersbot' => [
                'apiPrivateUrl' => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiPublicUrl'  => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiUser'       => 'tradersbot',
                'apiPassword'   => 'da55a3d5f8723d5f342b49abf34edb9c',
            ],
            'BenjaminRanford' => [
                'apiPrivateUrl' => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiPublicUrl'  => 'http://ipaygateway.com/ax_mail_system/api.php',
                'apiUser'       => 'benjaminranford',
                'apiPassword'   => 'da55a3d5f8723d5f342b49abf34edb9c',
            ],
        ],
        'Aweber' => [
            'Tradersbot' => [
                'apiPrivateUrl' => 'https://api.aweber.com/1.0/accounts/1013995',
                'apiPublicUrl' => 'https://api.aweber.com/1.0',
                'apiUser' => 'lorenk',
                'apiPassword' => 'Nnr2vEY5E',
                'otherCredentials' => [
                    'OAUth' => [
                        'consumer_key'    => 'AkbnqSjGHkR2hEhKIf0Lfp1Q',
                        'consumer_secret' => 's09Veatlv2fWqhsleTknBKHAkf6xvUuhEBZYOEvl',
                        'token'           => 'AgcztWYKokmkXcHkas21vrRS',
                        'token_secret'    => 'WPamKaV9aanupnqnM3J3BnEIFegDSzrAVOjcL3lo',
                    ],
                ],
            ],
            'BenjaminRanford' => [
                'apiPrivateUrl' => 'https://api.aweber.com/1.0/accounts/1013995',
                'apiPublicUrl' => 'https://api.aweber.com/1.0',
                'apiUser' => 'lorenk',
                'apiPassword' => 'Nnr2vEY5E',
                'otherCredentials' => [
                    'OAUth' => [
                        'consumer_key'    => 'AkbnqSjGHkR2hEhKIf0Lfp1Q',
                        'consumer_secret' => 's09Veatlv2fWqhsleTknBKHAkf6xvUuhEBZYOEvl',
                        'token'           => 'AgcztWYKokmkXcHkas21vrRS',
                        'token_secret'    => 'WPamKaV9aanupnqnM3J3BnEIFegDSzrAVOjcL3lo',
                    ],
                ],
            ],
        ],
        'Transact' => [
            'Ultratrade' => [
                'apiPrivateUrl' => 'https://api2.silverpop.com/XMLAPI',
                'apiPublicUrl'  => 'https://api2.silverpop.com/XMLAPI',
                'apiUser'       => 'transact@migfin.com',
                'apiPassword'   => 'Liron@123',
                'otherCredentials' => [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => 'd308b0d7-4afa-4b84-ae0f-f5d2c5f1126f',
                    'client_secret' => '76544b52-0294-479a-962a-cb630aebf4fb',
                    'refresh_token' => 'rmY-BZ3yLsNw9BvD4-XpK9Hak7Gu5qBNpViGOow6vu3gS1',
                    'engage_server' => 2,
                    'token_url'   => 'https://api2.silverpop.com/oauth/token',
                ],
            ],
            'ixoption' => [
                'apiPrivateUrl' => 'https://api2.silverpop.com/XMLAPI',
                'apiPublicUrl'  => 'https://api2.silverpop.com/XMLAPI',
                'apiUser'       => 'transact@migfin.com',
                'apiPassword'   => 'Liron@123',
                'otherCredentials' => [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => 'd308b0d7-4afa-4b84-ae0f-f5d2c5f1126f',
                    'client_secret' => '76544b52-0294-479a-962a-cb630aebf4fb',
                    'refresh_token' => 'rmY-BZ3yLsNw9BvD4-XpK9Hak7Gu5qBNpViGOow6vu3gS1',
                    'engage_server' => 2,
                    'token_url'   => 'https://api2.silverpop.com/oauth/token',
                ],
            ],
            'toroption' => [
                'apiPrivateUrl' => 'https://api2.silverpop.com/XMLAPI',
                'apiPublicUrl'  => 'https://api2.silverpop.com/XMLAPI',
                'apiUser'       => 'transact@migfin.com',
                'apiPassword'   => 'Liron@123',
                'otherCredentials' => [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => 'd308b0d7-4afa-4b84-ae0f-f5d2c5f1126f',
                    'client_secret' => '76544b52-0294-479a-962a-cb630aebf4fb',
                    'refresh_token' => 'rmY-BZ3yLsNw9BvD4-XpK9Hak7Gu5qBNpViGOow6vu3gS1',
                    'engage_server' => 2,
                    'token_url'   => 'https://api2.silverpop.com/oauth/token',
                ],
            ],
            'Tradersbot' => [
                'apiPrivateUrl' => 'https://api2.silverpop.com/XMLAPI',
                'apiPublicUrl'  => 'https://api2.silverpop.com/XMLAPI',
                'apiUser'       => 'transact@migfin.com',
                'apiPassword'   => 'Liron@123',
                'otherCredentials' => [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => 'd308b0d7-4afa-4b84-ae0f-f5d2c5f1126f',
                    'client_secret' => '76544b52-0294-479a-962a-cb630aebf4fb',
                    'refresh_token' => 'rmY-BZ3yLsNw9BvD4-XpK9Hak7Gu5qBNpViGOow6vu3gS1',
                    'engage_server' => 2,
                    'token_url'   => 'https://api2.silverpop.com/oauth/token',
                ],
            ],
            'BenjaminRanford' => [
                'apiPrivateUrl' => 'https://api2.silverpop.com/XMLAPI',
                'apiPublicUrl'  => 'https://api2.silverpop.com/XMLAPI',
                'apiUser'       => 'transact@migfin.com',
                'apiPassword'   => 'Liron@123',
                'otherCredentials' => [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => 'd308b0d7-4afa-4b84-ae0f-f5d2c5f1126f',
                    'client_secret' => '76544b52-0294-479a-962a-cb630aebf4fb',
                    'refresh_token' => 'rmY-BZ3yLsNw9BvD4-XpK9Hak7Gu5qBNpViGOow6vu3gS1',
                    'engage_server' => 2,
                    'token_url'   => 'https://api2.silverpop.com/oauth/token',
                ],
            ],
        ],
        'SpotOption' => [
            'Ultratrade' => [
                'apiPrivateUrl' => 'http://api-spotplatform.ultratrade.com/Api',
                'apiPublicUrl'  => 'https://spotplatform.ultratrade.com',
                'apiUser'       => 'araneum_n',
                'apiPassword'   => 'Ow50KQdh0t',
            ],
            'ixoption' => [
                'apiPrivateUrl' => 'http://api-spotplatform.ixoption.com/Api',
                'apiPublicUrl'  => 'https://spotplatform.ixoption.com',
                'apiUser'       => 'araneum',
                'apiPassword'   => 'wU7tc2YKg2',
            ],
            'toroption' => [
                'apiPrivateUrl' => 'http://api-spotplatform.toroption.com/Api',
                'apiPublicUrl'  => 'http://spotplatform.toroption.com',
                'apiUser'       => 'Torsite',
                'apiPassword'   => 'XmG7s5lJa9',
            ],
            'Tradersbot' => [
                'apiPrivateUrl' => 'http://api-spotplatform.titantrade.com/Api',
                'apiPublicUrl'  => 'https://spotplatform.titantrade.com',
                'apiUser'       => 'funnelrobot',
                'apiPassword'   => '56ceb9e964a7c',
            ],
            'BenjaminRanford' => [
                'apiPrivateUrl' => 'http://api-spotplatform.titantrade.com/Api',
                'apiPublicUrl'  => 'https://spotplatform.titantrade.com',
                'apiUser'       => 'funnelrobot',
                'apiPassword'   => '56ceb9e964a7c',
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            'Araneum\Base\Tests\Fixtures\Data\ApplicationData',
        ];
    }

    /**
     * Fixtures for agent
     *
     * @param ObjectManager $manager
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createRealAgents('application', $manager);
        $this->createRealAgents('appIxoption', $manager);
        $this->createRealAgents('appToroption', $manager);
        $this->createRealAgents('appTradersbot', $manager);
        $this->createRealAgents('appBenjaminRanford', $manager);
    }

    /**
     * Create real agent if credentials for application exist
     *
     * @param string        $appReference
     * @param ObjectManager $manager
     * {@inheritDoc}
     */
    public function createRealAgents($appReference, $manager)
    {
        $application = $this->getReference($appReference);
        if (!empty($application)) {
            $apies = self::APIES_CREDENTIALS;
            $appName = $application->getName();
            foreach ($apies as $apiName => $credentials) {
                if (!empty($credentials[$appName])) {
                    $agent = $manager
                        ->getRepository('AraneumAgentBundle:Agent')
                        ->findOneBy([
                            'application' => $application,
                            'type' => $apiName,
                        ]);
                    if (empty($agent)) {
                        $agent = new Agent();
                    }

                    $agent
                        ->setType($apiName)
                        ->setApplication($application)
                        ->setPrivateUrl($credentials[$appName]['apiPrivateUrl'])
                        ->setPublicUrl($credentials[$appName]['apiPublicUrl'])
                        ->setApiPassword($credentials[$appName]['apiPassword'])
                        ->setApiUser($credentials[$appName]['apiUser']);
                    if (!empty($credentials[$appName]['otherCredentials'])) {
                        $agent->setOtherCredentials($credentials[$appName]['otherCredentials']);
                    }

                    $manager->persist($agent);
                    $manager->flush();
                }
            }
        }
    }
}
