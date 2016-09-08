<?php
namespace FormHandler\Renderer;


class Tag
{
    /**
     * Name of this HTML tag.
     * @var string
     */
    protected $name;

    /**
     * Associative array with name => value items
     * @var array
     */
    protected $attributes = [];

    /**
     * The inner HTML of this tag, empty by default
     * @var string
     */
    protected $innerHtml = '';

    /**
     * Tag constructor.
     * @param $name
     * @param string $innerHtml
     */
    public function __construct($name, $innerHtml = '')
    {
        $this->name = $name;
        $this->innerHtml = $innerHtml;
    }

    /**
     * Add an attribute.
     * If the attribute exist, the value will be merged with the existing value
     * (concatting the value).
     * Note: No extra space is added!
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addAttribute($name, $value = '')
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
     * @return $this
     */
    public function setAttribute($name, $value = '')
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

        return '';
    }

    public function render()
    {
        $str = sprintf('<%s', $this->name);

        foreach ($this->attributes as $name => $value) {
            if ($value) {
                $str .= sprintf(' %s="%s"', $name, $value);
            }
        }

        if ($this->innerHtml) {
            $str .= sprintf('>%s</%s>', $this->innerHtml, $this->name);
        } else {
            $str .= '/>';
        }

        return $str;
    }
}