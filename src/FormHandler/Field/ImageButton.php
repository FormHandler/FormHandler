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
     * @var string
     */
    protected $src;

    /**
     * Alternative text which is shown in the button when the image could not be displayed.
     * @var string
     */
    protected $alt;

    /**
     * ImageButton constructor.
     * @param Form $form
     * @param string $src
     */
    public function __construct(Form &$form, $src = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if ($src) {
            $this->setSrc($src);
        }
    }

    /**
     * Set the the alternative text for the button
     *
     * @param string $value
     * @return ImageButton
     */
    public function setAlt($value)
    {
        $this->alt = $value;
        return $this;
    }

    /**
     * Return the alternative text for the button
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set the image source
     *
     * @param string $value
     * @return ImageButton
     */
    public function setSrc($value)
    {
        $this->src = $value;
        return $this;
    }

    /**
     * Return the image source
     *
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Return string representation of this field
     *
     * @return string
     */
    public function render()
    {
        $str = '<input type="image"';

        if (!empty($this->name)) {
            $str .= ' name="' . $this->name . '"';
        }

        if (!empty($this->src)) {
            $str .= ' src="' . $this->src . '"';
        }

        if (!empty($this->alt)) {
            $str .= ' alt="' . htmlentities($this->alt, ENT_QUOTES, 'UTF-8') . '"';
        }

        if (!empty($this->size)) {
            $str .= ' size="' . $this->size . '"';
        }

        if ($this->disabled !== null && $this->disabled) {
            $str .= ' disabled="disabled"';
        }

        $str .= parent::render();
        $str .= ' />';

        return $str;
    }
}
