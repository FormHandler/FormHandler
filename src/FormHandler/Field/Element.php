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
    protected $accesskey;

    /**
     * Specifies one or more classnames for an element (refers to a class in a style sheet)
     *
     * @var string
     */
    protected $class;

    /**
     * The unique id of this HTML element
     *
     * @var string
     */
    protected $id;

    /**
     * The value of the "style" attribute of this HTML element
     *
     * @var string
     */
    protected $style;

    /**
     * The value of the "title" attribute of this HTML element
     *
     * @var string
     */
    protected $title;

    /**
     * The value of the "tabindex" attribute of this HTML element
     *
     * @var int
     */
    protected $tabindex;

    /**
     * Associative array with name => value items
     * @var array
     */
    protected $attributes = [];

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
     * Return the associative array with key > value pairs which represent the attributes
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get the tabindex of this element
     *
     * @return int
     */
    public function getTabindex()
    {
        return $this->tabindex;
    }

    /**
     * Set the tab index for this element and return an instance to itsself
     *
     * @param int $index
     * @return $this
     */
    public function setTabindex($index)
    {
        $this->tabindex = $index;
        return $this;
    }

    /**
     * Returns the access key of this element
     *
     * @return string
     */
    public function getAccesskey()
    {
        return $this->accesskey;
    }

    /**
     * Set the access key for this element and return an instance to itsself
     *
     * @param string $key
     * @return $this
     */
    public function setAccesskey($key)
    {
        $this->accesskey = $key;
        return $this;
    }

    /**
     * Append a style string to the current value.
     *
     * @param string $style
     * @return $this
     */
    public function addStyle($style)
    {
        $this->style .= $style;
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
     * Set's the css class and return an instance to itsself
     *
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
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
     * Set the style for this element
     *
     * @param string $style
     * @return string
     */
    public function setStyle($style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Adds a class and return an instance to itsself.
     * If the class attribute was already filled, we will
     * put a space between the current value and the appended new value.
     *
     * @param string $class
     * @return $this
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
     * Return the title of this element
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title and return an instance to itsself
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
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
     * @return $this
     */
    public function setId($id)
    {
        $this->id = trim($id);
        return $this;
    }

    /**
     * Return a rendered object if it's called as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render a field and return the HTML
     * @return string
     */
    abstract public function render();
}
