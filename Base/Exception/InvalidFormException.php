<?php

namespace Araneum\Base\Exception;

use Symfony\Component\Form\FormInterface;

/**
 * Class InvalidFormException
 *
 * @package Araneum\Base\Exception
 */
class InvalidFormException extends \Exception
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * Constructor
     *
     * @param FormInterface   $form
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(FormInterface $form, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->form = $form;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Return message form
     *
     * @return mixed
     */
    public function getFormErrorMessage()
    {
        return (string) $this->form->getErrors(true, false);
    }

    /**
     * Get form
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
