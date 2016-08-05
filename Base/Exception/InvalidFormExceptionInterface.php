<?php

namespace Araneum\Base\Exception;

use Symfony\Component\Form\Form;

/**
 * Interface InvalidFormExceptionInterface
 *
 * @package Araneum\Base\Exception
 */
interface InvalidFormExceptionInterface
{
    /**
     * Return message form
     *
     * @param  Form $form
     * @return mixed
     */
    public function getFormErrorMessage(Form $form);
}
