<?php

namespace FormHandler\Concerns;

use Exception;
use FormHandler\Form;

trait HasFormAttributes
{
    /**
     * The action of the Form (location where the form is sent to).
     * When the action is empty, it will be posted to itself (default)
     *
     * @var string
     */
    protected string $action = '';

    /**
     * The target window where the form should post to
     *
     * @var string
     * @deprecated
     */
    protected string $target = '';

    /**
     * The name of the form.
     *
     * @var string
     */
    protected string $name = '';

    /**
     * The method how the form is submitted. Can either be POST or GET
     *
     * @var string
     */
    protected string $method = Form::METHOD_POST;

    /**
     * The encoding type of the form. Use one of the ENCTYPE_* constants.
     *
     * @var string
     */
    protected string $enctype = Form::ENCTYPE_URLENCODED;

    /**
     * Specifies the character encodings that are to be used for the form submission
     *
     * @var string
     */
    protected string $acceptCharset = '';

    /**
     * Not supported in HTML5.
     * Specifies a comma-separated list of file types that the server accepts
     * (that can be submitted through the file upload)
     *
     * @var string
     * @deprecated
     */
    protected string $accept = '';

    /**
     * Return mime-types of files that can be submitted through a file upload.
     *
     * Specifies a comma-separated list of file types that the server accepts
     * (that can be submitted through the file upload)
     *
     * @return string
     * @deprecated
     */
    public function getAccept(): string
    {
        return $this->accept;
    }

    /**
     * Set the mime-types of files that can be submitted through a file upload
     * and return the Form reference
     *
     * @param string $accept
     *
     * @return Form
     * @deprecated
     */
    public function setAccept(string $accept): Form
    {
        $this->accept = $accept;

        return $this;
    }

    /**
     * Return how form-data should be encoded before sending it to a server
     *
     * @return string
     */
    public function getEnctype(): string
    {
        return $this->enctype;
    }

    /**
     * Set how form-data should be encoded before sending it to a server
     * Possible values:
     * - application/x-www-form-urlencoded
     * - multipart/form-data
     * - text/plain
     *
     * @param string $enctype
     *
     * @return Form
     * @throws \Exception
     */
    public function setEnctype(string $enctype): Form
    {
        $enctype = strtolower(trim($enctype));

        if (in_array($enctype, [
            Form::ENCTYPE_MULTIPART,
            Form::ENCTYPE_PLAIN,
            Form::ENCTYPE_URLENCODED,
        ])) {
            $this->enctype = $enctype;
        } else {
            throw new Exception('Incorrect enctype given!');
        }

        return $this;
    }

    /**
     * Returns the character-sets the server can handle for form-data
     *
     * @return string
     */
    public function getAcceptCharset(): string
    {
        return $this->acceptCharset;
    }

    /**
     * Specifies the character-sets the server can handle for form-data
     * Returns the Form reference
     *
     * @param string $charset
     *
     * @return Form
     */
    public function setAcceptCharset(string $charset): Form
    {
        $this->acceptCharset = $charset;

        return $this;
    }

    /**
     * Return the name of the form
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the form and return the Form reference
     *
     * @param string $name
     *
     * @return Form
     */
    public function setName(string $name): Form
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the target
     *
     * @return string
     * @see http://www.w3schools.com/tags/tag_form.asp
     * @deprecated
     *
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Set the target and return the Form reference
     *
     * @param string $target
     *
     * @return Form
     * @see http://www.w3schools.com/tags/tag_form.asp
     * @deprecated
     * @deprecated
     *
     */
    public function setTarget(string $target): Form
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Return the action of this form
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set the action of this form and return the Form reference.
     * NOTE: The action is added in the HTML without any escaping!
     * Make sure YOU have escaped possible dangerous characters!
     *
     * @param string $action
     *
     * @return Form
     */
    public function setAction(string $action): Form
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Return the method how this form should be submitted
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the method how this form should be submitted
     *
     * @param string $method
     *
     * @return Form
     * @throws \Exception
     */
    public function setMethod(string $method): Form
    {
        $method = strtolower($method);

        if (in_array($method, [Form::METHOD_GET, Form::METHOD_POST])) {
            $this->method = $method;
        } else {
            throw new Exception('Incorrect method value given');
        }

        return $this;
    }
}