<?php

namespace FormHandler\Field;

/**
 */
class Optgroup extends Element
{
    use TraitFormAware;

    /**
     * List of the options in this optgroup
     *
     * @var array
     */
    protected array $options = [];

    /**
     * The label of this optgroup
     *
     * @var string
     */
    protected string $label = '';

    /**
     * Indicator if this optgroup is disabled or not
     *
     * @var boolean
     */
    protected bool $disabled = false;

    /**
     * Optgroup constructor.
     *
     * @param string $label
     */
    public function __construct(string $label)
    {
        $this->setLabel($label);
    }

    /**
     * Add an option to this optgroup
     *
     * @param Option $option
     *
     * @return $this
     */
    public function addOption(Option $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Add more options to this optgroup
     *
     * @param array $options
     *
     * @return $this
     */
    public function addOptions(array $options): self
    {
        foreach ($options as $option) {
            $this->options[] = $option;
        }

        return $this;
    }

    /**
     * Set the options with an assoc array
     *
     * @param array   $options
     * @param boolean $useArrayKeyAsValue
     *
     * @return $this
     */
    public function setOptionsAsArray(array $options, bool $useArrayKeyAsValue = true): self
    {
        $this->options = [];

        foreach ($options as $value => $label) {
            $option = new Option();
            $option->setLabel($label);
            $option->setValue($useArrayKeyAsValue ? $value : $label);

            $this->options[] = $option;
        }

        return $this;
    }

    /**
     * Add options with an assoc array
     *
     * @param array   $options
     * @param boolean $useArrayKeyAsValue
     *
     * @return $this
     */
    public function addOptionsAsArray(array $options, bool $useArrayKeyAsValue = true): self
    {
        foreach ($options as $value => $label) {
            $option = new Option();
            $option->setLabel($label);
            $option->setValue($useArrayKeyAsValue ? $value : $label);

            $this->options[] = $option;
        }

        return $this;
    }

    /**
     * Return the options which are located in this optgroup
     *
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the options of this optgroup
     *
     * @param array $options
     *
     * @return Optgroup
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get if this optgroup is disabled or not
     *
     * @return boolean
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Set if this optgroup is disabled or not
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Return the label of this optgroup
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set the label for this optgroup
     *
     * @param string $label
     *
     * @return Optgroup
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Return the HTML field formatted
     *
     * @return string
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
