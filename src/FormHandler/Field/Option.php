<?php
namespace FormHandler\Field;

use FormHandler\Form;

/**
 * This class represents an Option HTML element
 */
class Option extends Element
{
    use TraitFormAware;

    /**
     * Is this option disabled?
     * @var bool
     */
    protected $disabled = false;

    /**
     * The label of this option
     * @var string
     */
    protected $label;

    /**
     * Is this option selected?
     * @var bool
     */
    protected $selected = false;

    /**
     * The value for this option
     * @var string
     */
    protected $value;

    /**
     * Option constructor.
     * @param string $value
     * @param string $label
     */
    public function __construct($value = null, $label = null)
    {
        if ($value !== null) {
            $this->setValue($value);
        }

        if ($label !== null) {
            $this->setLabel($label);
        }
    }

    /**
     * Get if this option is disabled or not
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set if this option is disabled or not
     *
     * @param bool $disabled
     * @return Option
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Return the label of this option
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set a different label for this option.
     * When not set, the value will be used as label
     *
     * @param string $label
     * @return Option
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Return if this option is selected or not
     *
     * @return string
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * Set if this option is selected.
     *
     * @param bool $selected
     * @return Option
     */
    public function setSelected($selected)
    {
        $this->selected = (boolean)$selected;
        return $this;
    }

    /**
     * Return the value of this option
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of this option.
     * When selected, this value will be submitted
     *
     * @param string $value
     * @return Option
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Return the HTML field formatted
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Return string representation of this field
     *
     * @return string
     */
    public function render()
    {
        if ($this->getForm() instanceof Form) {
            return $this->getForm()->getRenderer()->render($this);
        }

        return '';
    }
}
