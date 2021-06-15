<?php

namespace FormHandler\Field;

use FormHandler\Form;

/**
 * With this class you can create an UploadField.
 *
 * When you add this to a form, the form's enctype will be set to multipart/form-data automatically.
 * Also, a hidden field called MAX_FILE_SIZE is added to the form object to let the browser
 * know the max files we can handle.
 *
 * Validation can be done with the UploadValidator. For more information about the UploadValidator,
 * {@see form/validator/UploadValidator.php}
 *
 * After uploading, the getValue() method returns an array like this:
 *
 * ```
 * Array
 * (
 *     [name] => Map3.xlsx
 *     [type] => application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
 *     [tmp_name] => C:\Windows\Temp\php7675.tmp
 *     [error] => 0
 *     [size] => 11784
 * )
 * ```
 *
 * You can enable multiple upload files like this:
 * ```php
 * $form -> uploadField('file')
 *       -> setMultiple( true );
 * ```
 *
 * When setting an uploadfield to allow multiple file uploads, it's name will automatically be changed to
 * include two square brackets. So the name of the field in the example above will become "file[]".
 *
 * After submitting a form with an uploadfield accepting multiple files, you will receive a result
 * from the getValue() method like this:
 *
 * ```
 * Array
 * (
 *     [name] => Array
 *     (
 *         [0] => Map3.xlsx
 *         [1] => payments-AT.xml
 *         [2] => status.xml
 *     )
 *
 *     [type] => Array
 *     (
 *         [0] => application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
 *         [1] => text/xml
 *         [2] => text/xml
 *     )
 *
 *     [tmp_name] => Array
 *     (
 *         [0] => C:\Windows\Temp\phpA65C.tmp
 *         [1] => C:\Windows\Temp\phpA66D.tmp
 *         [2] => C:\Windows\Temp\phpA66E.tmp
 *     )
 *
 *     [error] => Array
 *     (
 *         [0] => 0
 *         [1] => 0
 *         [2] => 0
 *     )
 *
 *     [size] => Array
 *     (
 *         [0] => 11784
 *         [1] => 17934
 *         [2] => 9968
 *     )
 *
 * )
 * ```
 *
 * After uploading a file, you can use the FormUtils {@see FormUtils.php} for the
 * most common actions (like moving an uploaded file, do some image mutations, etc).
 */
class UploadField extends AbstractFormField
{
    /**
     * The size of this field
     *
     * @var int|null
     */
    protected ?int $size;

    /**
     * The value of this field (as it is submitted)
     *
     * @var array
     */
    protected $value = [];

    /**
     * A list of mime types of files which we accept
     *
     * @var string|null
     */
    protected ?string $accept;

    /**
     * When set to true, we allow multiple files to be uploaded by this field.
     *
     * @var bool
     */
    protected bool $multiple = false;

    // allow multiple files to be uploaded by 1 uploadfield?

    /**
     * @throws \Exception
     */
    public function __construct(Form $form, string $name = '')
    {
        $this->form = $form;
        $this->form->setEnctype(Form::ENCTYPE_MULTIPART);
        $this->form->addField($this);

        if (!empty($name)) {
            $this->setName($name);
        }
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
        if (array_key_exists($name, $_FILES)) {
            $this->setValue($_FILES[$name]);
        }

        return $this;
    }

    /**
     * Returns true if the form was submitted and there was a file uploaded.
     *
     * @return boolean
     */
    public function isUploaded(): bool
    {
        if ($this->form->isSubmitted() && is_array($this->value) && $this->value['error'] == UPLOAD_ERR_OK) {
            return true;
        }

        return false;
    }

    /**
     * Get the types of files that can be submitted through a file upload
     *
     * @return null|string
     */
    public function getAccept(): ?string
    {
        return $this->accept;
    }

    /**
     * Specifies the types of files that can be submitted through a file upload
     * Example: text/html, image/jpeg, audio/mpeg, video/quicktime, text/css, and text/javascript
     *
     * @param string $mimeType
     *
     * @return $this
     */
    public function setAccept(string $mimeType): self
    {
        $this->accept = $mimeType;

        return $this;
    }

    /**
     * Return the size of the field
     *
     * @return null|int
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Set the size of the field and return the UploadField reference
     *
     * @param int $size
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Allow multiple files to be uploaded by 1 uploadfield?
     * Get the value for multiple
     *
     * @return boolean
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * allow multiple files to be uploaded by 1 uploadfield?
     * Set the value for multiple
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setMultiple(bool $value): self
    {
        $this->multiple = $value;

        return $this;
    }
}
