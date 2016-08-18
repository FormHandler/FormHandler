<?php
namespace FormHandler\Field;

use FormHandler;

/**
 * Create a select form.
 *
 * It's recommended to add the validator "IsOptionValidator", to
 * check if the submitted value is also one of the options which is added.
 */
class SelectField extends AbstractFormField
{

    protected $multiple;

    protected $size;

    // used to remember the current value
    protected $value;

    /**
     * The options of this selectfield
     *
     * @var ArrayObject
     */
    protected $options;

    /**
     * Create a new SelectField
     *
     * @param FormHandler\Form $form
     * @param string $name
     */
    public function __construct(FormHandler\Form &$form, $name = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if (! empty($name)) {
            $this->setName($name);
        }

        $this->options = new ArrayObject();
    }

    /**
     * Set the name
     *
     * @param string $name
     * @return SelectField
     */
    public function setName($name)
    {
        $this->name = $name;
        // after we know the name, try to get the value for this field.
        $this->setValue($this->form->getFieldValue($this->name));
        return $this;
    }

    /**
     * Set the options of this selectfield
     *
     * @param ArrayObject $options
     * @return SelectField
     */
    public function setOptions(ArrayObject $options)
    {
        $this->options = $options;

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Set the options with an assoc array
     *
     * @param array $options
     * @param boolean $useArrayKeyAsValue
     * @return SelectField
     */
    public function setOptionsAsArray($options, $useArrayKeyAsValue = true)
    {
        $this->options = new ArrayObject();

        foreach ($options as $value => $label) {
            $option = new Option();
            $option->setLabel($label);
            $option->setValue($useArrayKeyAsValue ? $value : $label);

            $this->options->append($option);
        }

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Add options with an assoc array
     *
     * @param array|ArrayObject $options
     * @param boolean $useArrayKeyAsValue
     * @return SelectField
     */
    public function addOptionsAsArray($options, $useArrayKeyAsValue = true)
    {
        if (! $this->options) {
            $this->options = new ArrayObject();
        }

        if (is_array($options) || $options instanceof ArrayAccess) {
            foreach ($options as $value => $label) {
                $option = new Option();
                $option->setLabel($label);
                $option->setValue($useArrayKeyAsValue ? $value : $label);

                $this->options->append($option);
            }
        }

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Add a option to this selectfield
     *
     * @param Option $option
     * @return SelectField
     */
    public function addOption(Option $option)
    {
        if (! $this->options) {
            $this->options = new ArrayObject();
        }
        $this->options->append($option);

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Add an optgroup to the options
     *
     * @param Optgroup $optgroup
     * @return SelectField
     */
    public function addOptgroup(Optgroup $optgroup)
    {
        $this->options->append($optgroup);

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Add more options to this selectfield
     *
     * @param ArrayObject $options
     * @return SelectField
     */
    public function addOptions(ArrayObject $options)
    {
        foreach ($options as $option) {
            $this->options->append($option);
        }
        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Return the options which are located in this selectfield
     *
     * @return ArrayObject
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Specifies that multiple options can be selected
     *
     * @param bool $multiple
     * @return SelectField
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * Return if multiple options can be selected or not
     *
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Specifies the number of visible options in a drop-down list
     *
     * @param int $size
     * @return SelectField
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get the number of visible options in a drop-down list
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the value for this field and return the SelectField reference.
     * We do not yet set the option as selected, because it's likeley that the
     * options are not loaded into this field yet.
     *
     * @param mixed $value
     * @return SelectField
     */
    public function setValue($value)
    {
        if ($value instanceof ArrayObject) {
            $value = $value->getArrayCopy();
        }

        $this->value = $value;

        // this will auto select the options based on this fields value
        $this->selectOptionsFromValue();

        return $this;
    }

    /**
     * Return the value for this field.
     * This will return an array if the field
     * is setup as "multiple". Otherwise it will return a string with the selected value.
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
            } elseif ($option instanceof Optgroup) {
                $options = $option->getOptions();
                foreach ($options as $option2) {
                    if ($option2->isSelected()) {
                        $selected[] = $option2->getValue();
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
     * Return the selected option
     *
     * @return ArrayObject|Option
     */
    public function getOptionByValue($value)
    {
        $selected = new ArrayObject();

        // walk all options
        foreach ($this->options as $option) {
            if ($option instanceof Option) {
                if ($option->getValue() == $value) {
                    $selected->append($option);
                }
            } elseif ($option instanceof Optgroup) {
                $options = $option->getOptions();
                foreach ($options as $option2) {
                    if ($option2->getValue() == $value) {
                        $selected->append($option2);
                    }
                }
            }
        }

        if ($selected->count() > 0) {
            if ($this->isMultiple()) {
                return $selected;
            } else {
                // return last selected item as value
                return $selected->offsetGet($selected->count() - 1);
            }
        }

        return null;
    }

    /**
     * Remove option by value
     *
     * @return void
     */
    public function removeOptionByValue($value)
    {
        // walk all options
        $options = $this->options;

        // Empty options;
        $this->options = new ArrayObject();

        foreach ($options as $option) {
            if ($option instanceof Option) {
                if ($option->getValue() != $value) {
                    $this->options->append($option);
                }
            } elseif ($option instanceof Optgroup) {
                $subOptions = new ArrayObject();
                $options2 = $option->getOptions();
                foreach ($options2 as $option2) {
                    if ($option2->getValue() != $value) {
                        $subOptions->append($option2);
                    }
                }

                if ($subOptions->count()) {
                    $this->options->append($subOptions);
                }
            }
        }
    }

    /**
     * Return string representation of this field
     *
     * @return string
     */
    public function render()
    {
        $str = '<select';

        if (! empty($this->name)) {
            $suffix = ($this->isMultiple() && substr($this->name, - 1) != ']' ? "[]" : "");
            $str .= ' name="' . $this->name . $suffix . '"';
        }

        if ($this->isMultiple()) {
            $str .= ' multiple="multiple"';
        }

        if (! empty($this->size)) {
            $str .= ' size="' . $this->size . '"';
        }

        if ($this->disabled !== null && $this->disabled) {
            $str .= ' disabled="disabled"';
        }

        $str .= parent::render();
        $str .= '>';

        $value = is_array($this->value) ? $this->value : array(
            (string) $this->value
        );

        // walk all options
        foreach ($this->options as $option) {
            // set selected if the value matches
            if ($option instanceof Option) {
                $option->setSelected(in_array((string) $option->getValue(), $value));
            } elseif ($option instanceof Optgroup) {
                $options = $option->getOptions();
                foreach ($options as $option2) {
                    $option2->setSelected(in_array((string) $option2->getValue(), $value));
                }
            }

            $str .= $option->render();
        }

        $str .= '</select>';
        return $str;
    }

    /**
     * This function will use the "value" for this field to
     * select the correct options
     */
    protected function selectOptionsFromValue()
    {
        if (! $this->options) {
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
                if ($options) {
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
    }
}
