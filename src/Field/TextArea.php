<?php

namespace FormHandler\Field;

use FormHandler\Form;

/**
 */
class TextArea extends AbstractFormField
{
    /**
     *
     * The number of cols (columns) of this textarea
     *
     * @var int
     */
    protected int $cols = 40;

    /**
     * The number of rows in this textarea
     *
     * @var int
     */
    protected int $rows = 7;

    /**
     * Is this field readonly?
     *
     * @var bool
     */
    protected bool $readonly = false;

    /**
     * The value of this field
     *
     * @var string
     */
    protected $value = '';

    /**
     * The placeholder for this field when it does not have a value.
     *
     * @var string
     */
    protected string $placeholder = '';

    /**
     * The max length of the field
     *
     * @var int|null
     */
    protected ?int $maxlength;

    public function __construct(Form $form, string $name, int $cols = 40, int $rows = 7)
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
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        $this->setValue($this->form->getFieldValue($this->name));

        return $this;
    }

    /**
     * Get the visible width of a text-area
     *
     * @return int
     */
    public function getCols(): int
    {
        return $this->cols;
    }

    /**
     * Specifies the visible width of a text-area
     *
     * @param int $cols
     *
     * @return $this
     */
    public function setCols(int $cols): self
    {
        $this->cols = $cols;

        return $this;
    }

    /**
     * Get the visible number of rows in a text-area
     *
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * Specifies the visible number of rows in a text-area
     *
     * @param int $rows
     *
     * @return $this
     */
    public function setRows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Return the max length of this field
     *
     * @return int|null
     */
    public function getMaxlength(): ?int
    {
        return $this->maxlength;
    }

    /**
     * Set the max length of this field and return the TextArea reference
     *
     * @param int $maxlength
     *
     * @return $this
     */
    public function setMaxlength(int $maxlength): self
    {
        $this->maxlength = $maxlength;
        $this->maxlength = $maxlength;

        return $this;
    }

    /**
     * Return the readonly status of this field
     *
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    /**
     * Set if this field is readonly and return the TextField reference
     *
     * @param bool $readonly
     *
     * @return TextArea
     */
    public function setReadonly(bool $readonly): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    /**
     * Get the value for placeholder
     *
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * Set the value for placeholder
     *
     * @param string $value
     *
     * @return $this
     */
    public function setPlaceholder(string $value): self
    {
        $this->placeholder = $value;

        return $this;
    }
}
