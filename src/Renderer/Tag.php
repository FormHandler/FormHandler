<?php

namespace FormHandler\Renderer;

class Tag
{
    /**
     * Name of this HTML tag.
     *
     * @var string
     */
    protected string $name;

    /**
     * Associative array with name => value items
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * The inner HTML of this tag, empty by default
     *
     * @var string
     */
    protected string $innerHtml = '';

    /**
     * Tag constructor.
     *
     * @param string $name
     * @param string $innerHtml
     */
    public function __construct(string $name, string $innerHtml = '')
    {
        $this->setName($name);
        $this->innerHtml = $innerHtml;
    }

    /**
     * Set an attribute.
     * If the attribute exists, it will be overwritten
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setAttribute(string $name, string $value = ''): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function render(): string
    {
        $str = sprintf('<%s', $this->name);

        foreach ($this->attributes as $name => $value) {
            if ($value) {
                $str .= sprintf(' %s="%s"', $name, $value);
            }
        }

        if ($this->innerHtml || $this->name == 'textarea') {
            $str .= sprintf('>%s</%s>', $this->innerHtml, $this->name);
        } else {
            $str .= '/>';
        }

        return $str;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Tag
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getInnerHtml(): string
    {
        return $this->innerHtml;
    }

    /**
     * @param string $innerHtml
     *
     * @return Tag
     */
    public function setInnerHtml(string $innerHtml): self
    {
        $this->innerHtml = $innerHtml;

        return $this;
    }
}
