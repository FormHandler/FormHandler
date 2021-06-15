<?php

namespace FormHandler\Concerns;

use ReflectionClass;
use FormHandler\Form;
use FormHandler\Field\Element;
use FormHandler\Field\AbstractFormField;

trait HasFields
{
    /**
     * List of all fields in this form
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * Remove a field from the form
     *
     * @param Element $field
     *
     * @return Form
     */
    public function removeField(Element $field): Form
    {
        foreach ($this->fields as $i => $elem) {
            if ($elem == $field) {
                unset($this->fields[$i]);
                break;
            }
        }

        return $this;
    }

    /**
     * Remove all fields by the given name.
     * If there are more than 1 field with the given name, then all will be removed.
     *
     * @param string $name
     *
     * @return Form
     */
    public function removeAllFieldsByName(string $name): Form
    {
        foreach ($this->fields as $i => $field) {
            if ($field->getName() == $name) {
                unset($this->fields[$i]);
            }
        }

        return $this;
    }

    /**
     * Remove a field from the form by the name of the field
     *
     * @param string $id
     *
     * @return Form
     */
    public function removeFieldById(string $id): Form
    {
        foreach ($this->fields as $i => $field) {
            if ($field->getId() == $id) {
                unset($this->fields[$i]);
                break;
            }
        }

        return $this;
    }

    /**
     * Return a field by its ID, or null when it's not found.
     *
     * @param string $id
     *
     * @return AbstractFormField
     */
    public function getFieldById(string $id): ?AbstractFormField
    {
        foreach ($this->fields as $field) {
            if ($field->getId() == $id) {
                return $field;
            }
        }

        return null;
    }

    /**
     * add a field to this form, so that it can be retrieved by the method getFieldByName
     *
     * @param Element $field
     *
     * @return \FormHandler\Form
     */
    public function addField(Element $field): Form
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Return a list of fields which have the name which equals the given name
     *
     * @param string $className
     *
     * @return AbstractFormField[]
     * @throws \ReflectionException
     */
    public function getFieldsByClass(string $className): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            if ($field instanceof $className ||
                get_class($field) == $className ||
                (new ReflectionClass($field))->getShortName() == $className
            ) {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Return a list of fields which have the name which equals the given name
     *
     * @param string $name
     *
     * @return AbstractFormField[]
     */
    public function getFieldsByName(string $name): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            if ($field->getName() == $name) {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Return an array with all fields from this form.
     * This method will return an array.
     * When there are no fields, it will return an empty array.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Remove a field from the form by the name of the field.
     * If there are more than 1 field with the given name, then only the first one will
     * be removed.
     *
     * @param string $name
     *
     * @return Form
     */
    public function removeFieldByName(string $name): Form
    {
        foreach ($this->fields as $i => $field) {
            if ($field->getName() == $name) {
                unset($this->fields[$i]);
                break;
            }
        }

        return $this;
    }

    /**
     * Return a field by its name. We will return null if it's not found.
     *
     * @param string $name
     *
     * @return AbstractFormField|null
     */
    public function getFieldByName(string $name): ?AbstractFormField
    {
        foreach ($this->fields as $field) {
            if ($field->getName() == $name) {
                return $field;
            }
        }

        return null;
    }
}