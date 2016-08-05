<?php

namespace Araneum\Base\Tests\Fixtures\Data;

use Araneum\Bundle\ApiV2Bundle\Entity\Agent;
use Araneum\Bundle\ApiV2Bundle\Entity\Api;
use Araneum\Bundle\ApiV2Bundle\Entity\CredentialKey;
use Araneum\Bundle\ApiV2Bundle\Entity\CredentialValue;
use Araneum\Bundle\ApiV2Bundle\Entity\FieldKey;
use Araneum\Bundle\ApiV2Bundle\Entity\Object;
use Araneum\Bundle\ApiV2Bundle\Entity\RestMethod;
use Araneum\Bundle\MainBundle\Entity\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LyrebirdApiData
 *
 * @package Araneum\Base\Tests\Fixtures\Data
 */
class LyrebirdApiData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var
     */
    private $container;
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var string
     */
    const AGENT_NAME = 'Lyrebird';

    /**
     * @var string
     */
    const APISSYSTEM_API_NAME = 'APISSYSTEM_LYREBIRD_API';

    /**
     * @var array
     */
    const APIES = [
        self::APISSYSTEM_API_NAME => [
            'application' => 'apissystem',
            'baseUrl' => 'http://master.lyrebird.office.dev',
            'credentials' => [
                self::CREDENTIAL_USERNAME => 'apiLyrebirdUser',
                self::CREDENTIAL_PASSWORD => 'puje&rUv*M6GUsp',
            ],
        ],
    ];

    /**
     * @var string
     */
    const CREDENTIAL_PASSWORD = 'password';
    /**
     * @var string
     */
    const CREDENTIAL_USERNAME = 'username';

    /**
     * @var array
     */
    const CREDENTIAL_KEYS = [
        self::CREDENTIAL_PASSWORD => self::CREDENTIAL_PASSWORD,
        self::CREDENTIAL_USERNAME => self::CREDENTIAL_USERNAME,
    ];

    /**
     * @var string
     */
    const FILED_MODULE  = 'moduleName';

    /**
     * @var string
     */
    const FILED_BRAND   = 'brandName';

    /**
     * @var string
     */
    const FILED_COMMAND = 'commandName';

    /**
     * @var array
     */
    const FIELD_KEYS = [
        self::FILED_MODULE => self::FILED_MODULE,
        self::FILED_BRAND => self::FILED_BRAND,
        self::FILED_COMMAND => self::FILED_COMMAND,
    ];

    /**
     * @var string
     */
    const REST_METHOD_VIEW   = 'LYREBIRD_VIEW';

    /**
     * @var string
     */
    const REST_METHOD_BATCH  = 'LYREBIRD_BATCH';

    /**
     * @var string
     */
    const REST_METHOD_CUSTOM = 'LYREBIRD_CUSTOM';

    /**
     * @var array
     */
    const REST_METHODS = [
        self::REST_METHOD_VIEW => self::REST_METHOD_VIEW,
        self::REST_METHOD_BATCH => self::REST_METHOD_BATCH,
        self::REST_METHOD_CUSTOM => self::REST_METHOD_CUSTOM,
    ];

    /**
     * @var array
     */
    const RELATIVE_URLS = [
        self::REST_METHOD_VIEW => 'api/{brandName}/{moduleName}/{commandName}/',
        self::REST_METHOD_BATCH => 'api/{brandName}/Batch/',
        self::REST_METHOD_CUSTOM => 'api/{moduleName}/{commandName}/',
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
     * Fixtures for Lyrebird apies
     *
     * @param ObjectManager $manager
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->createCredentialKeys();
        $this->createFieldKeys();
        $this->createObjects();
        $this->createRestMethods();
        $this->createAgent();
        $this->createApies();
    }

    /**
     * {@inheritDoc}
     */
    private function createCredentialKeys()
    {
        foreach (self::CREDENTIAL_KEYS as $name) {
            $credentialKey = $this->manager
                ->getRepository('AraneumApiV2Bundle:CredentialKey')
                ->findOneByName($name);

            if (empty($credentialKey)) {
                $credentialKey = (new CredentialKey())
                    ->setName($name);
                $this->manager->persist($credentialKey);
                $this->manager->flush();
            }

            $this->addReference($name.'CredentialKey', $credentialKey);
        }
    }

    /**
     * {@inheritDoc}
     */
    private function createFieldKeys()
    {
        foreach (self::FIELD_KEYS as $name) {
            $fieldKey = $this->manager
                ->getRepository('AraneumApiV2Bundle:FieldKey')
                ->findOneByName($name);

            if (empty($fieldKey)) {
                $fieldKey = (new FieldKey())
                    ->setName($name)
                    ->setType(FieldKey::TYPE_STRING)
                ;
                $this->manager->persist($fieldKey);
                $this->manager->flush();
            }

            $this->addReference($name.'FieldKey', $fieldKey);
        }
    }

    /**
     * {@inheritDoc}
     */
    private function createObjects()
    {
        $object = $this->manager
            ->getRepository('AraneumApiV2Bundle:Object')
            ->findOneByName(self::REST_METHOD_VIEW);

        if (empty($object)) {
            $object = (new Object())
                ->setName(self::REST_METHOD_VIEW)
                ->setFieldKeys(new ArrayCollection([
                    $this->getReference(self::FILED_BRAND.'FieldKey'),
                    $this->getReference(self::FILED_MODULE.'FieldKey'),
                    $this->getReference(self::FILED_COMMAND.'FieldKey'),
                ]))
            ;
            $this->manager->persist($object);
            $this->manager->flush();
        }

        $this->addReference(self::REST_METHOD_VIEW.'Object', $object);

        $object = $this->manager
            ->getRepository('AraneumApiV2Bundle:Object')
            ->findOneByName(self::REST_METHOD_BATCH);

        if (empty($object)) {
            $object = (new Object())
                ->setName(self::REST_METHOD_BATCH)
                ->setFieldKeys(new ArrayCollection([
                    $this->getReference(self::FILED_BRAND.'FieldKey'),
                ]))
            ;
            $this->manager->persist($object);
            $this->manager->flush();
        }

        $this->addReference(self::REST_METHOD_BATCH.'Object', $object);

        $object = $this->manager
            ->getRepository('AraneumApiV2Bundle:Object')
            ->findOneByName(self::REST_METHOD_CUSTOM);

        if (empty($object)) {
            $object = (new Object())
                ->setName(self::REST_METHOD_CUSTOM)
                ->setFieldKeys(new ArrayCollection([
                    $this->getReference(self::FILED_MODULE.'FieldKey'),
                    $this->getReference(self::FILED_COMMAND.'FieldKey'),
                ]))
            ;
            $this->manager->persist($object);
            $this->manager->flush();
        }

        $this->addReference(self::REST_METHOD_CUSTOM.'Object', $object);
    }

    /**
     * {@inheritDoc}
     */
    private function createRestMethods()
    {
        foreach (self::REST_METHODS as $method) {
            $restMethod = $this->manager
                ->getRepository('AraneumApiV2Bundle:RestMethod')
                ->findOneByName($method);

            if (empty($restMethod)) {
                $restMethod = (new RestMethod())
                    ->setName($method)
                    ->setObject($this->getReference($method.'Object'))
                    ->setType(RestMethod::TYPE_POST)
                    ->setRelativeUrl(self::RELATIVE_URLS[$method])
                ;
                $this->manager->persist($restMethod);
                $this->manager->flush();
            }

            $this->addReference($method.'RestMethod', $restMethod);
        }
    }

    /**
     * {@inheritDoc}
     */
    private function createAgent()
    {
        $agent = $this->manager
            ->getRepository('AraneumApiV2Bundle:Agent')
            ->findOneByName(self::AGENT_NAME);

        if (empty($agent)) {
            $agent = (new Agent())
                ->setName(self::AGENT_NAME)
                ->setRestMethods(new ArrayCollection(
                    [
                        $this->getReference(self::REST_METHOD_VIEW.'RestMethod'),
                        $this->getReference(self::REST_METHOD_BATCH.'RestMethod'),
                        $this->getReference(self::REST_METHOD_CUSTOM.'RestMethod'),
                    ]
                ))
                ->setAuthType(Agent::AGENT_AUTH_HTTP_BASIC)
                ->setCredentialKeys(new ArrayCollection(
                    [
                        $this->getReference(self::CREDENTIAL_USERNAME.'CredentialKey'),
                        $this->getReference(self::CREDENTIAL_PASSWORD.'CredentialKey'),
                    ]
                ))
            ;
            $this->manager->persist($agent);
            $this->manager->flush();
        }

        $this->addReference(self::AGENT_NAME.'Agent', $agent);
    }

    /**
     * {@inheritDoc}
     */
    private function createApies()
    {
        foreach (self::APIES as $name => $settings) {
            $api = $this->manager
                ->getRepository('AraneumApiV2Bundle:Api')
                ->findOneByName($name);

            if (empty($api)) {
                $application = $this->manager
                    ->getRepository('AraneumMainBundle:Application')
                    ->findOneByName($settings['application']);

                $api = (new Api())
                    ->setName($name)
                    ->setApplication($application)
                    ->setBaseUrl($settings['baseUrl'])
                    ->setAgent($this->getReference(self::AGENT_NAME.'Agent'))
                ;

                $this->manager->persist($api);

                foreach ($settings['credentials'] as $key => $value) {
                    $credentialValue = (new CredentialValue())
                        ->setApi($api)
                        ->setCredentialKey($this->getReference($key.'CredentialKey'))
                        ->setValue($value)
                    ;
                    $this->manager->persist($credentialValue);
                }

                $this->manager->flush();
            }
        }
    }
}
