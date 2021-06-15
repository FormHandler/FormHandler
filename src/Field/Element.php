<?php

namespace FormHandler\Field;

/**
 * This is a basic HTML element which already includes all
 * global HTML attributes.
 * See also [this link](http://www.w3schools.com/tags/ref_standardattributes.asp)
 */
abstract class Element
{
    /**
     * Specifies a shortcut key to activate/focus an element
     *
     * @var string
     */
    protected string $accesskey = '';

    /**
     * Specifies one or more classnames for an element (refers to a class in a style sheet)
     *
     * @var string
     */
    protected string $class = '';

    /**
     * The unique ID of this HTML element
     *
     * @var string
     */
    protected string $id = '';

    /**
     * The value of the "style" attribute of this HTML element
     *
     * @var string
     */
    protected string $style = '';

    /**
     * The value of the "title" attribute of this HTML element
     *
     * @var string
     */
    protected string $title = '';

    /**
     * The value of the "tabindex" attribute of this HTML element
     *
     * @var int|null
     */
    protected ?int $tabindex = null;

    /**
     * Associative array with name => value items
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Add an attribute.
     * If the attribute exist, the value will be merged with the existing value
     * (concatenating the value).
     * Note: No extra space is added!
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addAttribute(string $name, string $value = ''): self
    {
        $org = $this->getAttribute($name);
        if ($org) {
            $value = trim($org . $value);
        }
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Get an attribute.
     * When the attribute does not exist, we will return an empty array.
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttribute(string $name): string
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return '';
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

    /**
     * Return the associative array with key > value pairs which represent the attributes
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the tabindex of this element
     *
     * @return int|null
     */
    public function getTabindex(): ?int
    {
        return $this->tabindex;
    }

    /**
     * Set the tab index for this element and return an instance to itself
     *
     * @param int $index
     *
     * @return $this
     */
    public function setTabindex(int $index): self
    {
        $this->tabindex = $index;

        return $this;
    }

    /**
     * Returns the access key of this element
     *
     * @return string
     */
    public function getAccesskey(): string
    {
        return $this->accesskey;
    }

    /**
     * Set the access key for this element and return an instance to itself
     *
     * @param string $key
     *
     * @return $this
     */
    public function setAccesskey(string $key): self
    {
        $this->accesskey = $key;

        return $this;
    }

    /**
     * Append a style string to the current value.
     *
     * @param string $style
     *
     * @return $this
     */
    public function addStyle(string $style): self
    {
        $this->style .= $style;

        return $this;
    }

    /**
     * Get the class(ses) which are set for this element
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Set's the css class and return an instance to itself
     *
     * @param string $class
     *
     * @return $this
     */
    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Return the style set for this element
     *
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * Set the style for this element
     *
     * @param string $style
     *
     * @return $this
     */
    public function setStyle(string $style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Adds a class and return an instance to itself.
     * If the class attribute was already filled, we will
     * put a space between the current value and the appended new value.
     *
     * @param string $class
     *
     * @return $this
     */
    public function addClass(string $class): self
    {
        if (empty($this->class)) {
            $this->class = trim($class);
        } else {
            $this->class .= " " . trim($class);
        }

        return $this;
    }

    /**
     * Return the title of this element
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the title and return an instance to itself
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the ID of this element
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the ID of this element and return an instance to itself
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = trim($id);

        return $this;
    }

    /**
     * Return a rendered object if it's called as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Render a field and return the HTML
     *
     * @return string
     */
    abstract public function render(): string;
}
