<?php
namespace FormHandler\Field;

/**
 */
class TextArea extends AbstractFormField
{

    protected $cols;

    protected $rows;

    protected $readonly;

    protected $value;

    protected $placeholder;

    public function __construct(Form &$form, $name, $cols = 40, $rows = 7)
    {
        $this->form = $form;
        $this->form->addField($this);

        $this->setName($name);
        $this->setCols($cols);
        $this->setRows($rows);
    }

    /**
     * Set the name
     *
     * @param string $name
     * @return TextArea
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setValue($this->form->getFieldValue($this->name));
        return $this;
    }

    /**
     * Specifies the visible width of a text-area
     *
     * @param int $cols
     */
    public function setCols($cols)
    {
        $this->cols = (integer) $cols;
        return $this;
    }

    /**
     * Specifies the visible number of rows in a text-area
     *
     * @param int $rows
     */
    public function setRows($rows)
    {
        $this->rows = (integer) $rows;
        return $this;
    }

    /**
     * Get the visible width of a text-area
     *
     * @return int
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * Get the visible number of rows in a text-area
     *
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Set the max length of this field and return the TextField reference
     *
     * @param int $maxlength
     * @return TextField
     */
    public function setMaxlength($maxlength)
    {
        $this->maxlength = (integer) $maxlength;
        return $this;
    }

    /**
     * Return the max length of this field
     *
     * @return int
     */
    public function getMaxlength()
    {
        return $this->maxlength;
    }

    /**
     * Set if this field is readonly and return the TextField reference
     *
     * @param bool $readonly
     * @return TextField
     */
    public function setReadonly($readonly)
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * Return the readonly status of this field
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->readonly;
    }

    /**
     * Set the value for this field and return the TextField reference
     *
     * @param string $value
     * @return TextField
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Return the value for this field
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value for placeholder
     *
     * @param string $value
     * @return TextField
     */
    public function setPlaceholder($value)
    {
        $this->placeholder = $value;
        return $this;
    }

    /**
     * Get the value for placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Return string representation of this field
     *
     * @return string
     */
    public function render()
    {
        $str = '<textarea';
        $str .= ' cols="' . $this->cols . '"';
        $str .= ' rows="' . $this->rows . '"';

        if (! empty($this->name)) {
            $str .= ' name="' . $this->name . '"';
        }

        if ($this->disabled !== null && $this->disabled) {
            $str .= ' disabled="disabled"';
        }

        if (! empty($this->maxlength)) {
            $str .= ' maxlength="' . $this->maxlength . '"';
        }

        if ($this->readonly !== null && $this->readonly) {
            $str .= ' readonly="readonly"';
        }

        if ($this->placeholder) {
            $str .= ' placeholder="' . htmlspecialchars($this->placeholder) . '"';
        }

        $str .= parent::render();
        $str .= '>';

        if (! empty($this->value)) {
            $str .= htmlspecialchars($this->value);
        }
        $str .= '</textarea>';

        return $str;
    }
}
