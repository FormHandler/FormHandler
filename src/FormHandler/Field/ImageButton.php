<?php
namespace FormHandler\Field;

use FormHandler\Form;

/**
 * This class will create an image button
 */
class ImageButton extends Element
{
    /**
     * The form object where this image button is located in.
     * @var Form
     */
    protected $form;

    /**
     * Is this button disabled?
     * @var bool
     */
    protected $disabled = false;

    /**
     * The name of the button
     * @var string
     */
    protected $name;

    /**
     * The size of the button
     * @var int
     */
    protected $size;

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
     * Return the form instance of this field
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set if this field is disabled and return the ImageButton reference
     *
     * @param bool $disabled
     * @return ImageButton
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Return if this field is disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set the name of the field and return the ImageButton reference
     *
     * @param string $name
     * @return ImageButton
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the name of the ImageButton
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the size of the field and return the ImageButton reference
     *
     * @param int $size
     * @return ImageButton
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Return the size of the field
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
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

        if (! empty($this->name)) {
            $str .= ' name="' . $this->name . '"';
        }

        if (! empty($this->src)) {
            $str .= ' src="' . $this->src . '"';
        }

        if (! empty($this->alt)) {
            $str .= ' alt="' . htmlspecialchars($this->alt) . '"';
        }

        if (! empty($this->size)) {
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
