<?php

namespace Araneum\Base\Tests\Fixtures\User;

use Araneum\Bundle\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\RadBundle\DataFixtures\AbstractFixture;

/**
 * Class UserFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\User
 */
class UserFixtures extends AbstractFixture implements FixtureInterface
{
    const TEST_USER_NAME            = 'TestUserName';
    const TEST_USER_EMAIL           = 'test@user.email';
    const TEST_USER_FULLNAME        = 'TestUserFullName';
    const TEST_USER_PASSWORD        = 'TestUserPassword';
    const TEST_USER_ENABLED         = true;
    const TEST_USER_ROLES           = [User::ROLE_USER];
    const TEST_USER_NAME_DELETE     = 'TestUserNameForDelete';
    const TEST_USER_EMAIL_FILTER    = 'testuseremailforfilter@test.com';
    const TEST_USER_FULLNAME_FILTER = 'TestUserFullnameForFilter';
    const TEST_USER_ENABLED_FILTER  = true;
    const TEST_USER_NAME_UPDATE     = 'TestUserNameForUpdate';
    const TEST_USER_EMAIL_UPDATE    = 'testuseremailforupdate@test.com';

    const TEST_API_INTERNAL_USER_NAME = 'ApiInternalUser';
    const TEST_API_INTERNAL_EMAIL     = 'test.apiuser.internal@araneum.dev';
    const TEST_API_INTERNAL_PASSWORD  = 'apiInternalPassword';

    const ADMIN_USER_NAME     = "AdminName";
    const ADMIN_USER_PASSWORD = 'admin_123';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        $user = $manager->getRepository('AraneumUserBundle:User')
            ->findOneByUsername(self::TEST_USER_NAME);
        if (empty($user)) {
            $user = new User();
            $user->setUsername(self::TEST_USER_NAME);
            $user->setEmail(self::TEST_USER_EMAIL);
            $user->setFullName(self::TEST_USER_FULLNAME);
            $user->setPlainPassword(self::TEST_USER_PASSWORD);
            $user->setEnabled(self::TEST_USER_ENABLED);
            $user->setRoles(self::TEST_USER_ROLES);
            $manager->persist($user);
        }
        $this->addReference('testOwner', $user);

        $userForDelete = $manager->getRepository('AraneumUserBundle:User')
            ->findOneByUsername(self::TEST_USER_NAME_DELETE);
        if (empty($userForDelete)) {
            $userForDelete = new User();
            $userForDelete->setUsername(self::TEST_USER_NAME_DELETE);
            $userForDelete->setEmail('testusernamefordelete@test.com');
            $userForDelete->setFullName(self::TEST_USER_FULLNAME);
            $userForDelete->setPlainPassword(self::TEST_USER_PASSWORD);
            $userForDelete->setEnabled(self::TEST_USER_ENABLED);
            $userForDelete->setRoles(self::TEST_USER_ROLES);
            $manager->persist($userForDelete);
        }

        $userForFilter = $manager->getRepository('AraneumUserBundle:User')
            ->findOneByEmail(self::TEST_USER_EMAIL_FILTER);
        if (empty($userForFilter)) {
            $userForFilter = new User();
            $userForFilter->setUsername('TestUserEmailForFilter');
            $userForFilter->setEmail(self::TEST_USER_EMAIL_FILTER);
            $userForFilter->setFullName(self::TEST_USER_FULLNAME_FILTER);
            $userForFilter->setPlainPassword(self::TEST_USER_PASSWORD);
            $userForFilter->setEnabled(self::TEST_USER_ENABLED_FILTER);
            $userForFilter->setRoles(self::TEST_USER_ROLES);
            $userForFilter->setCreatedAt(new \DateTime('1980-11-25'));
            $manager->persist($userForFilter);
        }

        $userForUpdate = $manager->getRepository('AraneumUserBundle:User')
            ->findOneByEmail(self::TEST_USER_EMAIL_UPDATE);
        if (empty($userForUpdate)) {
            $userForUpdate = new User();
            $userForUpdate->setUsername(self::TEST_USER_NAME_UPDATE);
            $userForUpdate->setEmail(self::TEST_USER_EMAIL_UPDATE);
            $userForUpdate->setFullName(self::TEST_USER_FULLNAME);
            $userForUpdate->setPlainPassword(self::TEST_USER_PASSWORD);
            $userForUpdate->setEnabled(self::TEST_USER_ENABLED);
            $userForUpdate->setRoles(self::TEST_USER_ROLES);
            $manager->persist($userForUpdate);
        }

        $emailUser = $manager->getRepository('AraneumUserBundle:User')->findOneByUsername('emailuser');
        if (empty($emailUser)) {
            $emailUser = new User();
            $emailUser->setUsername('emailuser');
            $emailUser->setPlainPassword('emailuser_123');
            $emailUser->setEmail('emailuser@araneum.dev');
            $emailUser->setRoles([User::ROLE_ADMIN]);
            $emailUser->setEnabled(true);
            $manager->persist($emailUser);
        }

        $emptyUser = $manager->getRepository('AraneumUserBundle:User')->findOneByUsername('emptyuser');
        if (empty($emptyUser)) {
            $emptyUser = new User();
            $emptyUser->setUsername('emptyuser');
            $emptyUser->setPlainPassword('emptyuser_123');
            $emptyUser->setEmail('emptyuser@araneum.dev');
            $emptyUser->setRoles([User::ROLE_ADMIN]);
            $emptyUser->setEnabled(true);
            $manager->persist($emptyUser);
        }

        $userApiInternal = $manager->getRepository('AraneumUserBundle:User')->findOneByUsername(
            self::TEST_API_INTERNAL_USER_NAME
        );
        if (empty($userApiInternal)) {
            $userApiInternal = new User();
            $userApiInternal->setUsername(self::TEST_API_INTERNAL_USER_NAME);
            $userApiInternal->setPlainPassword(self::TEST_API_INTERNAL_PASSWORD);
            $userApiInternal->setEmail(self::TEST_API_INTERNAL_EMAIL);
            $userApiInternal->setRoles([User::ROLE_API_INTERNAL]);
            $userApiInternal->setEnabled(true);
            $manager->persist($userApiInternal);
        }

        $adminUser = $manager->getRepository('AraneumUserBundle:User')->findOneByUsername(self::ADMIN_USER_NAME);
        if (empty($adminUser)) {
            $adminUser = new User();
            $adminUser->setUsername(self::ADMIN_USER_NAME);
            $adminUser->setPlainPassword(self::ADMIN_USER_PASSWORD);
            $adminUser->setEmail('adminuser@araneum.dev');
            $adminUser->setRoles([User::ROLE_ADMIN]);
            $adminUser->setEnabled(true);
            $manager->persist($adminUser);
        }

        $manager->flush();
    }
}
