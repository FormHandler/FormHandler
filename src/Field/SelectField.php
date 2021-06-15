<?php

namespace FormHandler\Field;

use FormHandler;
use FormHandler\Form;

/**
 * Create a select form.
 *
 * It's recommended to add the validator "IsOptionValidator", to
 * check if the submitted value is also one of the options which is added.
 */
class SelectField extends AbstractFormField
{
    /**
     * Set if we can select multiple values in this selectfield, or just one
     *
     * @var boolean
     */
    protected bool $multiple = false;

    /**
     * The size (number of options) which are displayed. By default, this is 1.
     *
     * In most of the cases this is only used when $multiple is set to true.
     *
     * @var int|null
     */
    protected ?int $size = null;

    /**
     * This is used to remember the current value.
     * This can be a string or an array, depending on the $multiple setting.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The options of this selectfield
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Create a new SelectField
     *
     * @param FormHandler\Form $form
     * @param string           $name
     */
    public function __construct(FormHandler\Form $form, string $name = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if (!empty($name)) {
            $this->setName($name);
        }

        $this->options = [];
    }

    /**
     * Set the name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        // after we know the name, try to get the value for this field.
        $this->setValue($this->form->getFieldValue($this->name));

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

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * This function will use the "value" for this field to
     * select the correct options
     */
    protected function selectOptionsFromValue(): void
    {
        if (empty($this->options)) {
            return;
        }

        // there is no current value known. So, just stop.
        if ($this->value === null) {
            return;
        }

        // walk all options
        foreach ($this->options as $option) {
            if ($option instanceof Option) {
                if (is_array($this->value)) {
                    $option->setSelected(in_array($option->getValue(), $this->value));
                } else {
                    $option->setSelected($option->getValue() == $this->value);
                }
            } elseif ($option instanceof Optgroup) {
                $options = $option->getOptions();
                foreach ($options as $option2) {
                    if (is_array($this->value)) {
                        $option2->setSelected(in_array($option2->getValue(), $this->value));
                    } else {
                        $option2->setSelected($option2->getValue() == $this->value);
                    }
                }
            }
        }
    }

    /**
     * Add options with an assoc array
     *
     * @param array   $options
     * @param boolean $useArrayKeyAsValue
     *
     * @return SelectField
     */
    public function addOptionsAsArray(array $options, bool $useArrayKeyAsValue = true): self
    {
        foreach ($options as $value => $label) {
            $option = new Option();
            $option->setLabel($label);
            $option->setValue($useArrayKeyAsValue ? $value : $label);

            $this->options[] = $option;
        }

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Add an option to this selectfield
     *
     * @param Option $option
     *
     * @return $this
     */
    public function addOption(Option $option): self
    {
        $this->options[] = $option;

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Add an optgroup to the options
     *
     * @param Optgroup $optgroup
     *
     * @return $this
     */
    public function addOptgroup(Optgroup $optgroup): self
    {
        $this->options[] = $optgroup;

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Add more options to this selectfield
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
        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Return the options which are located in this selectfield.
     * This can either be an Option, or an Optgroup object.
     *
     * @return array<Option|Optgroup>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the options of this selectfield
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Get the number of visible options in a drop-down list
     *
     * @return ?int
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Specifies the number of visible options in a drop-down list
     *
     * @param int $size
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Return the value for this field.
     * This will return an array if the field
     * is setup as "multiple". Otherwise, it will return a string with the selected value.
     *
     * @return array|string
     */
    public function getValue()
    {
        $selected = [];

        // walk all options
        foreach ($this->options as $option) {
            if ($option instanceof Option) {
                if ($option->isSelected()) {
                    $selected[] = $option->getValue();
                }
            } else {
                if ($option instanceof Optgroup) {
                    $options = $option->getOptions();
                    foreach ($options as $option2) {
                        if ($option2->isSelected()) {
                            $selected[] = $option2->getValue();
                        }
                    }
                }
            }
        }

        $count = sizeof($selected);
        if ($count > 0) {
            if ($this->isMultiple()) {
                return $selected;
            } else {
                // return last selected item as value
                return $selected[$count - 1];
            }
        }

        return $this->isMultiple() ? [] : "";
    }

    /**
     * Set the value for this field and return the SelectField reference.
     * We do not yet set the option as selected, because it's likely that the
     * options are not loaded into this field yet.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value): self
    {
        parent::setValue($value);

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Return if multiple options can be selected or not
     *
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Specifies that multiple options can be selected
     *
     * @param bool $multiple
     *
     * @return SelectField
     */
    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;
        $this->clearCache();

        return $this;
    }

    /**
     * Return the option which has the given value.
     * If there are multiple options with the same value, the first will be returned.
     *
     * @param string $value
     *
     * @return Option
     */
    public function getOptionByValue(string $value): ?Option
    {
        // walk all options
        foreach ($this->options as $option) {
            if ($option instanceof Option) {
                if ($option->getValue() == $value) {
                    return $option;
                }
            } else {
                if ($option instanceof Optgroup) {
                    $options = $option->getOptions();
                    foreach ($options as $option2) {
                        if ($option2->getValue() == $value) {
                            return $option2;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Remove option by value.
     *
     * In case that the option was nested in an Optgroup,
     * and it was the only option in that optgroup, then the
     * optgroup is also removed.
     *
     * @param string|int|float $value
     *
     * @return $this
     */
    public function removeOptionByValue($value): self
    {
        $value = (string)$value;
        // walk all options
        $options = $this->options;

        // Empty options;
        $this->options = [];

        foreach ($options as $option) {
            if ($option instanceof Option) {
                if ($option->getValue() != $value) {
                    $this->options[] = $option;
                }
            } else {
                if ($option instanceof Optgroup) {
                    $subOptions = [];
                    $options2   = $option->getOptions();
                    foreach ($options2 as $option2) {
                        if ($option2->getValue() != $value) {
                            $subOptions[] = $option2;
                        }
                    }

                    $option->setOptions($subOptions);

                    if (sizeof($subOptions) > 0) {
                        $this->options[] = $option;
                    }
                }
            }
        }

        return $this;
    }
}
