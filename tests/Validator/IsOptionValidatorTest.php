<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Field\Optgroup;
use FormHandler\Tests\TestCase;
use FormHandler\Validator\IsOptionValidator;

class IsOptionValidatorTest extends TestCase
{
    protected array $options = [
        'dog',
        'cat',
        'horse',
        'mouse',
        'dragon',
    ];

    public function testIsOptionRequired()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['pet'] = '';

        $form = new Form(null, false);

        $field = $form->selectField('pet')
            ->addOptionsAsArray($this->options, false);

        $validator = new IsOptionValidator(true);
        $field->setValidator($validator);

        $this->assertFalse(
            $field->isValid(),
            'Should be invalid as field has no value and its required'
        );

        $validator->setRequired(false);
        $field->setValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'Should be valid as field is empty and its not required'
        );
    }

    public function testValidOption()
    {
        // now set the value "dog"
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['pet'] = 'dog';

        $form = new Form('', false);

        $field = $form->selectField('pet')
            ->addOptionsAsArray($this->options, false)
            ->setValidator(new IsOptionValidator(true));

        $this->assertTrue(
            $field->isValid(),
            'Should be valid as field contains an value which is also an option'
        );
    }

    public function testInvalidOption()
    {
        // now set the value "DOG"
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['pet'] = 'DOG';

        $form = new Form('', false);

        $field = $form->selectField('pet')
            ->addOptionsAsArray($this->options, false)
            ->setValidator(new IsOptionValidator(true));

        $this->assertFalse(
            $field->isValid(),
            'Field is invalid because its not an option (case sensitive)'
        );
    }

    public function testMultipleOptions()
    {
        // now set multiple options
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['pet'] = array('dog', 'horse');

        $form = new Form('', false);

        $field = $form->selectField('pet')
            ->addOptionsAsArray($this->options, false)
            ->setValidator(new IsOptionValidator(true));

        $this->assertFalse(
            $field->isValid(),
            'Should be false as this field is not set as multiple, and its value is now an array.'
        );

        $field->setMultiple(true);
        $this->assertTrue(
            $field->isValid(),
            'Should be valid as field contains multiple values which are also options'
        );
    }

    public function testOptGroup()
    {
        // now set multiple options
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['pet'] = array('dog', 'dragon');

        $form = new Form('', false);

        $pets = new Optgroup('pets');
        $pets->addOptionsAsArray(['dog', 'cat', 'mouse'], false);

        $exotic = new Optgroup('exotic pets');
        $exotic->addOptionsAsArray(['snake', 'dragon', 'worm'], false);

        $field = $form->selectField('pet')
            ->addOptgroup($pets)
            ->addOptgroup($exotic)
            ->setValidator(new IsOptionValidator(true));

        $this->assertFalse(
            $field->isValid(),
            'Should be false as this field is not set as multiple, and its value is now an array.'
        );

        $field->setMultiple(true);
        $this->assertTrue(
            $field->isValid(),
            'Should be valid as field contains multiple values which are also options'
        );
    }

    /**
     * Test incorrect fields
     */
    public function testIncorrectField()
    {
        $this->expectException(\Exception::class);
        $this->expectDeprecationMessageMatches('/works on select fields/');

        $form = new Form(null, false);
        $form->textField('name')->addValidator(new IsOptionValidator(true));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $_POST = [];
    }
}
