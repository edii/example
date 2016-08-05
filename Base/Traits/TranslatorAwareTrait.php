<?php

namespace Araneum\Base\Traits;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Trait TranslatorAwareTrait
 *
 * @package Araneum\Base\Traits
 */
trait TranslatorAwareTrait
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Set translator
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Get translator
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}
