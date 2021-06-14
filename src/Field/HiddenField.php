<?php

namespace FormHandler\Field;

use FormHandler\Form;

/**
 */
class HiddenField extends AbstractFormField
{
    /**
     * @var string
     */
    protected $value;

    public function __construct(Form $form, string $name = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if (!empty($name)) {
            $this->setName($name);
        }
    }

    /**
     * Set the name
     *
     * @param string $name
     *
     * @return HiddenField
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        $this->setValue($this->form->getFieldValue($this->name));

        return $this;
    }
}
