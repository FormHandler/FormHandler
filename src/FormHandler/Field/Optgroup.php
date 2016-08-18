<?php
namespace FormHandler\Field;

/**
 */
class Optgroup extends Element
{
    use HasAttributes;

    /**
     * List of the options in this optgroup
     *
     * @var array
     */
    protected $options = [];

    /**
     * The label of this optgroup
     *
     * @var string
     */
    protected $label;

    /**
     * Indicator if this optgroup is disabled or not
     *
     * @var boolean
     */
    protected $disabled = false;

    public function __construct($label)
    {
        $this->setLabel($label);
    }

    /**
     * Set the options of this optgroup
     *
     * @param array $options
     * @return Optgroup
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Add a option to this optgroup
     *
     * @param Option $option
     * @return Optgroup
     */
    public function addOption(Option $option)
    {
        if (! $this->options) {
            $this->options = [];
        }
        $this->options[] = $option;
        return $this;
    }

    /**
     * Add more options to this optgroup
     *
     * @param array $options
     * @return Optgroup
     */
    public function addOptions(array $options)
    {
        if (! $this->options) {
            $this->options = [];
        }
        foreach ($options as $option) {
            $this->options[] = $option;
        }
        return $this;
    }

    /**
     * Set the options with an assoc array
     *
     * @param array $options
     * @param boolean $useArrayKeyAsValue
     * @return Optgroup
     */
    public function setOptionsAsArray(array $options, $useArrayKeyAsValue = true)
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
     * @param array $options
     * @param boolean $useArrayKeyAsValue
     * @return Optgroup
     */
    public function addOptionsAsArray(array $options, $useArrayKeyAsValue = true)
    {
        if (! $this->options) {
            $this->options = [];
        }

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
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get if this optgroup is disabled or not
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set if this optgroup is disabled or not
     *
     * @param bool $disabled
     * @return Optgroup
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Set the label for this optgroup
     *
     * @param string $label
     * @return Optgroup
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Return the label of this optgroup
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Return the HTML field formatted
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Return a string representation of this optgroup
     *
     * @return string
     */
    public function render()
    {
        $str = '<optgroup label="' . htmlspecialchars($this->label) . '"';

        if ($this->disabled !== null && $this->disabled) {
            $str .= ' disabled="disabled"';
        }

        if (! empty($this->id)) {
            $str .= ' id="' . $this->id . '"';
        }

        if (! empty($this->title)) {
            $str .= ' title="' . htmlspecialchars($this->title) . '"';
        }

        if (! empty($this->style)) {
            $str .= ' style="' . $this->style . '"';
        }

        if (! empty($this->class)) {
            $str .= ' class="' . $this->class . '"';
        }

        foreach ($this->attributes as $name => $value) {
            $str .= ' ' . $name . '="' . $value . '"';
        }

        $str .= '>';

        if ($this->options && $this->options->count() > 0) {
            foreach ($this->options as $option) {
                $str .= $option->render();
            }
        }

        $str .= '</optgroup>';

        return $str;
    }
}
