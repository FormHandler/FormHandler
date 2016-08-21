[![Build Status](https://travis-ci.org/teyeheimans/FormHandler.svg?branch=master)](https://travis-ci.org/teyeheimans/FormHandler)

FormHandler
======

This FormHandler is a PHP solution to generate form fields and validate them.
Making forms is in general a time-taking job. In this package we try to offer
a solution so that making forms is easy. 


FormHandler has a few assumptions:
  * A form is **always** submitted to itsself. That means, to the same script/page where the form is defined.

To create a form you have to:
  * Define the form and it's fields
  * Check if the form is submitted and if it's vald
  * Parse the form's fields in your HTML / view

A very basic example is:
```php

#
# This code defines your form and what to do with it when it's valid.
# This code is probably defined in your controller.
#

// Create the form
$form = new Form();

// Create a field in the form. Fluent method chaining is supported. 
$form -> textField('name')
      -> addValidator( new StringValidator( 2, 50, true, 'You have to supply your name (between 2 and 50 characters)' ) )
      -> setPlaceholder( 'Enter your name' );

// Check if the form is submitted
if( $form -> isSubmitted() )
{
    // Check if the form is valid.
    if( $form -> isValid() )
    {
        // Do your stuff here with the form, for example, store something
        // in a database.
    }
}
else
{
    // Here, the form is not yet submitted!
    // You could for example set some default values in the form.
}

#
# Then, in your view, you can use your form fields.
# All elements have a __toString method which will actually display their HTML tag.
#

// This will display the <form> html tag.
echo $form; 

// This will display the <input type=text> field.
echo $form -> getFieldByName('name');  

// You can mix plain old html with "dynamic" generated fields
// Of course you could also generate a SubmitButton object and use that one.
echo '<input type="submit" value="Submit" />';

```

So, this was our first basic example. Lets see what happens here. 

First we create the form and add a textfield called "name". We then check if the 
form is submitted and if it's valid. 

This is because we assume that every form is submitted to itsself. That means that
the first time this script will execute, it will not be submitted, and this ignore that part.


