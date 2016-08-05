<?php

namespace Araneum\Base\Tests\Fixtures\Mail;

use Araneum\Bundle\MailBundle\Entity\Mail;
use Araneum\Bundle\MainBundle\Entity\Cluster;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class MailFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Mail
 */
class MailFixtures extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{

    const TEST_MAIL_SENDER   = 'testsender@test.test';
    const TEST_MAIL_TARGET   = 'testtargetr@test.test';
    const TEST_MAIL_STATUS   = Mail::STATUS_NEW;
    const TEST_MAIL_HEADLINE = 'Test mail admin headline';
    const TEST_MAIL_TEXTBODY = 'Test mail admin text body';
    const TEST_MAIL_HTMLBODY = '<p>Test mail admin htmlbody</p>';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $mail = $manager->getRepository('AraneumMailBundle:Mail')->findOneBySender(self::TEST_MAIL_SENDER);
        if (empty($mail)) {
            $mail = new Mail();
            $mail->setApplication($this->getReference('testApplication'));
            $mail->setSender(self::TEST_MAIL_SENDER);
            $mail->setTarget(self::TEST_MAIL_TARGET);
            $mail->setStatus(self::TEST_MAIL_STATUS);
            $mail->setHeadline(self::TEST_MAIL_HEADLINE);
            $mail->setHtmlBody(self::TEST_MAIL_HTMLBODY);
            $mail->setTextBody(self::TEST_MAIL_TEXTBODY);
            $mail->setCreatedAt(new \DateTime('1980-11-25'));
            $mail->setSentAt(new \DateTime('1980-11-25'));

            $manager->persist($mail);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return ['Araneum\Base\Tests\Fixtures\Main\ApplicationFixtures'];
    }
}
