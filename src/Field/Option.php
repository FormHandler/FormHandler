<?php

namespace FormHandler\Field;

/**
 * This class represents an Option HTML element
 */
class Option extends Element
{
    use TraitFormAware;

    /**
     * Is this option disabled?
     *
     * @var bool
     */
    protected bool $disabled = false;

    /**
     * The label of this option
     *
     * @var string
     */
    protected string $label = '';

    /**
     * Is this option selected?
     *
     * @var bool
     */
    protected bool $selected = false;

    /**
     * The value for this option
     *
     * @var string
     */
    protected string $value = '';

    /**
     * Option constructor.
     *
     * @param string|int|float|null $value
     * @param string|int|float|null $label
     */
    public function __construct($value = null, $label = null)
    {
        if ($value !== null) {
            $this->setValue((string)$value);
        }

        if ($label !== null) {
            $this->setLabel((string)$label);
        }
    }

    /**
     * Get if this option is disabled or not
     *
     * @return boolean
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Set if this option is disabled or not
     *
     * @param bool $disabled
     *
     * @return Option
     */
    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Return the label of this option
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set a different label for this option.
     * When not set, the value will be used as label
     *
     * @param string|int|float $label
     *
     * @return Option
     */
    public function setLabel($label): self
    {
        $this->label = (string)$label;

        return $this;
    }

    /**
     * Return if this option is selected or not
     *
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * Set if this option is selected.
     *
     * @param bool $selected
     *
     * @return Option
     */
    public function setSelected(bool $selected): self
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Return the value of this option
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set the value of this option.
     * When selected, this value will be submitted
     *
     * @param string|int|float $value
     *
     * @return Option
     */
    public function setValue($value): self
    {
        $this->value = (string)$value;

        return $this;
    }

    /**
     * Return the HTML field formatted
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Return string representation of this field
     *
     * @return string
     */
    public function render(): string
    {
        return $this->getForm()->getRenderer()->render($this);
    }
}
