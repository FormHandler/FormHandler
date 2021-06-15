<?php

namespace FormHandler\Field;

use FormHandler\Form;

/**
 * This class will create an image button
 */
class ImageButton extends AbstractFormButton
{
    /**
     * The source of the image which is displayed in the button
     *
     * @var string
     */
    protected string $src = '';

    /**
     * Alternative text which is shown in the button when the image could not be displayed.
     *
     * @var string
     */
    protected string $alt = '';

    /**
     * ImageButton constructor.
     *
     * @param Form   $form
     * @param string $src
     */
    public function __construct(Form $form, string $src = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if ($src) {
            $this->setSrc($src);
        }
    }

    /**
     * Return the alternative text for the button
     *
     * @return string
     */
    public function getAlt(): string
    {
        return $this->alt;
    }

    /**
     * Set the alternative text for the button
     *
     * @param string $value
     *
     * @return ImageButton
     */
    public function setAlt(string $value): self
    {
        $this->alt = $value;

        return $this;
    }

    /**
     * Return the image source
     *
     * @return string
     */
    public function getSrc(): string
    {
        return $this->src;
    }

    /**
     * Set the image source
     *
     * @param string $value
     *
     * @return ImageButton
     */
    public function setSrc(string $value): self
    {
        $this->src = $value;

        return $this;
    }
}
