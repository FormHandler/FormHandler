<?php

namespace FormHandler\Field;

use FormHandler\Form;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 09-09-16
 * Time: 15:07
 */
trait TraitFormAware
{
    /**
     * The Form object
     * @var Form
     */
    protected $form;

    /**
     * Return the form instance of this field
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     * @return $this
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
        return $this;
    }
}
