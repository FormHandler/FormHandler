<?php

namespace FormHandler\Button;

/**
 * class Submit
 *
 * Create a submit button on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Button
 */
class Submit extends \FormHandler\Button\Button
{
    /**
     * Constructor: The constructor to create a new Submit object.
     *
     * @param object $form the form where this field is located on
     * @param string $name the name of the button
     * @return \FormHandler\Button\Submit
     * @author Teye Heimans
     */
    public function __construct($form, $name)
    {
        return parent::__construct($form, $name)
            ->setType(self::TYPE_SUBMIT)
            ->setCaption($form->_text(26));
    }
}