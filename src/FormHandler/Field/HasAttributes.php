<?php
namespace FormHandler\Field;

trait HasAttributes
{
    protected $attributes = [];

    /**
     * Add an attribute.
     * If the attribute exist, the value will be merged with the existing value
     * (concatting the value).
     * Note: No extra space is added!
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addAttribute($name, $value = "")
    {
        $org = $this->getAttribute($name);
        if ($org) {
            $value = trim($org . $value);
        }
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Set an attribute.
     * If the attribute exists, it will be overwritten
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setAttribute($name, $value = "")
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Get a attribute.
     * When the attribute does not exists, we will return an empty array.
     *
     * @param string $name
     * @return String
     */
    public function getAttribute($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return "";
    }
}
