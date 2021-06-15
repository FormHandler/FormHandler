<?php

namespace FormHandler\Field;

use FormHandler\Form;

trait TraitFormAware
{
    /**
     * The Form object
     *
     * @var Form
     */
    protected Form $form;

    /**
     * Return the form instance of this field
     *
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param Form $form
     *
     * @return $this
     */
    public function setForm(Form $form): self
    {
        $this->form = $form;

        return $this;
    }
}
