<?php
namespace FormHandler\Field;

/**
 */
class Option extends Element
{
    use HasAttributes;

    /**
     * The id of this option
     * @var string
     */
    protected $id;

    /**
     * A style string which is applied to this option
     * @var string
     */
    protected $style;

    /**
     * A classname which is applied to this option
     * @var
     */
    protected $class;

    /**
     * The title of this option
     * @var string
     */
    protected $title;

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
     * Set if this option is selected.
     *
     * @param bool $selected
     * @return Option
     */
    public function setSelected($selected)
    {
        $this->selected = (boolean) $selected;
        return $this;
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
     * Get if this option is disabled or not
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
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
     * Return if this option is selected or not
     *
     * @return string
     */
    public function isSelected()
    {
        return $this->selected;
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
     * Return the style set for this element
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set the style
     *
     * @param string $style
     * @return Element
     */
    public function setStyle($style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Get the class(ses) which are set for this element
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Adds a class and return an instance to itsself
     *
     * @param string $class
     * @return Element
     */
    public function addClass($class)
    {
        if (empty($this->class)) {
            $this->class = trim($class);
        } else {
            $this->class .= " " . trim($class);
        }

        return $this;
    }

    /**
     * Set's the css class and return an instance to itsself
     *
     * @param string $class
     * @return Element
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Set the title and return an instance to itsself
     *
     * @param string $title
     * @return Element
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Return the title of this element
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the id of this element
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id of this element and return an instance to itsself
     *
     * @param string $id
     * @return Element
     */
    public function setId($id)
    {
        $this->id = trim($id);
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
     * Return string representation
     *
     * @return string
     */
    public function render()
    {
        $str = '<option';

        if ($this->value !== null) {
            $str .= ' value="' . htmlentities($this->value, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($this->selected !== null && $this->selected) {
            $str .= ' selected="selected"';
        }

        if ($this->disabled !== null && $this->disabled) {
            $str .= ' disabled="disabled"';
        }

        if (! empty($this->id)) {
            $str .= ' id="' . $this->id . '"';
        }

        if (! empty($this->title)) {
            $str .= ' title="' . htmlentities($this->title, ENT_QUOTES, 'UTF-8') . '"';
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

        $str .= '>' . htmlentities(! empty($this->label) ? $this->label : $this->value, ENT_QUOTES, 'UTF-8');
        $str .= '</option>';

        return $str;
    }
}
