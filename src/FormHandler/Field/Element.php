<?php
namespace FormHandler\Field;

/**
 * This is a basic HTML element which already includes all
 * global HTML attributes.
 * See also [this link](http://www.w3schools.com/tags/ref_standardattributes.asp)
 */
class Element
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
     * @SuppressWarnings(PHPMD)
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
     * Get the tabindex of this element
     *
     * @return int
     */
    public function getTabindex()
    {
        return $this->tabindex;
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
     * Returns the access key of this element
     *
     * @return string
     */
    public function getAccesskey()
    {
        return $this->accesskey;
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
     * Return the style set for this element
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
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
     * @SuppressWarnings(PHPMD)
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
     * Return an string representation for this element
     * @return string
     */
    public function render()
    {
        $str = '';
        if (! empty($this->id)) {
            $str .= ' id="' . $this->id . '"';
        }

        if (! empty($this->title)) {
            $str .= ' title="' . $this->title . '"';
        }

        if (! empty($this->style)) {
            $str .= ' style="' . $this->style . '"';
        }

        if (! empty($this->class)) {
            $str .= ' class="' . $this->class . '"';
        }

        if (! empty($this->tabindex)) {
            $str .= ' tabindex="' . $this->tabindex . '"';
        }

        if (! empty($this->accesskey)) {
            $str .= ' accesskey="' . $this->accesskey . '"';
        }

        if (isset($this -> attributes)) {
            foreach ($this->attributes as $name => $value) {
                $str .= ' ' . $name . '="' . $value . '"';
            }
        }

        return $str;
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
}
