<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\DateValidator;

class DateValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the date validator
     */
    public function testDateValidator()
    {
        $form = new Form('', false);
        $field = $form->textField('date');

        $validator = new DateValidator(true);

        $field->setValidator($validator);
        $this->assertFalse(
            $field->isValid(),
            'Required field with empty value should be invalid'
        );

        $this->assertCount(1, $field->getErrorMessages());

        $errormsg = 'Invalid date given!';
        $validator->setRequired(false);
        $validator->setErrorMessage($errormsg);

        $field->setValidator($validator);
        $this->assertTrue(
            $field->isValid(),
            'Empty non-required field should be valid'
        );

        $valid = [
            'now',
            '10 September 2000',
            '+1 day',
            '+1 week',
            '+1 week 2 days 4 hours 2 seconds',
            'next Thursday',
            'last Monday',
            '2006-12-12 10:00:00.5 +1 week +1 hour',
            '12-05-2016',
            '2016-05-12'
        ];

        foreach ($valid as $value) {
            $field->setValue($value);
            $this->assertTrue(
                $field->isValid(),
                'Field should have valid date when given: ' . $value
            );
        }

        $invalid = ['Hi', 'Bogus'];
        foreach ($invalid as $value) {
            $field->setValue($value);
            $this->assertFalse(
                $field->isValid(),
                'Field should have invalid date when given: ' . $value
            );
        }

        $this->assertContains($errormsg, $field->getErrorMessages());
    }
}
