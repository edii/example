<?php

namespace Araneum\Base\Service;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;

/**
 * Class FormHandlerService
 *
 * @package Araneum\Base\Service
 */
class FormHandlerService
{
    /**
     * Extract children attributes from FormView to Array
     *
     * @param  array|FormView $formView
     * @param  array          $fields
     * @return array
     */
    public function extract(
        $formView,
        array $fields = [
            'name',
            'full_name',
            'label',
            'value',
        ]
    ) {
        $list = [];

        if ($formView instanceof FormView) {
            $formView = $formView->children;
        }

        foreach ($formView as $child) {
            if (!(count($child->children) > 0)) {
                $item = [];

                foreach ($fields as $field) {
                    if (!isset($child->vars[$field])) {
                        continue;
                    }

                    $item[$field] = $child->vars[$field];
                }

                $list[] = $item;
            } else {
                $list = $list + $this->extract($child->children, $fields);
            }
        }

        return $list;
    }

    /**
     * Get Form Errors
     *
     * @param  Form $form
     * @return array
     */
    public function getErrorMessages(Form $form)
    {
        $errors = [];

        foreach ($form->getErrors(true, true) as $error) {
            $message = $error->getMessage();

            if (in_array($message, $errors)) {
                continue;
            }

            $errors[] = $message;
        }

        return $errors;
    }
}
