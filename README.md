[![Build Status](https://travis-ci.org/teyeheimans/FormHandler.svg?branch=master)](https://travis-ci.org/teyeheimans/FormHandler)

FormHandler
======

This FormHandler is a PHP solution to generate form fields and validate them.
Making forms is in general a time-taking job. In this package we try to offer
a solution so that making forms is easy. 


FormHandler has a few assumptions:
  - A form is **always** submitted to itsself. That means, to the same script/page where the form is defined.
  - We assume that when you create a ```SubmitButton``` or ```ImageButton```, that you don't use 
    own HTML tag buttons.

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
    // You could for example set some predefined values in the form.
}

#
# Then, in your view, you can use your form fields.
#

// This will display the <form> html tag.
echo $form; 

// This will display the HTML tag for the "name" field.
echo $form('name');  

// You can mix plain old html with "dynamic" generated fields
// Of course you could also generate a SubmitButton object and use that one.
echo '<input type="submit" value="Submit" />';
```

So, this was our first basic example. Lets see what happens here. 

- First we create the form and add a textfield called "name". 
  We append a ```StringValidator``` to the field where we allow values between 2 and 50 characters.  
  We define that this field is required and if the field is invalid, we use a custom error message.

 
- Then we check if our form is submitted. If the form is not yet submitted, you could prefill your 
  fields with predefined values. This is usually the case for *edit* forms.
  On your first execution of this script, the form will not be submitted, so this part will be skipped.
  
- After checking if the form is submitted, we check if the form is valid. If the form is valid, 
  you can use the submitted values and process them (for example, store them in a database).
  When the submitted form is invalid, you could just ignore the values. The form will be rendered again,
  which will display the error message to the user about incorrect form fields.
     
- Finally, in our view we render the HTML. You are self responsible to render the fields where you want. 
  FormHandler does not mix with the design of your fields, except from some form related HTML tags like ```label```
  for a radio button.
  
Fluent Interface 
-----  

FormHandler implements a fluent interface. This means that you *can* use method chaining. It's not required though.

Example:
```php
$form = new Form();

// An example using method chaining.
$form -> textField( 'name' )
      -> setSize( 10 )
      -> setId( 'myName' )
      -> setTitle( 'Enter your name' )
      -> setPlaceholder( '<name here>' )
      -> addValidator( new StringValidator( 2, 50, true ) );
```


  
General methods 
-----

Most objects (the form, fields and the buttons) represent an HTML tag. All of these objects have some general 
available getters and setters to influence some of the attributes of the HTML tag. For example:

  * setTabindex(`$index`)
  * getTabindex()
  * setAccesskey(`$key`)
  * getAccesskey()
  * setStyle(`$style`)
  * addStyle(`$style`)
  * getStyle()
  * addClass(`$class`)
  * getClass()
  * setClass(`$class`)
  * setTitle(`$title`)
  * getTitle()
  * setId()
  * getId()
  


The `Form` Object
-----
 
This object represents the Form. It allows you to create fields in this form, retrieve fields, ask the status 
of the form (submitted, valid) and set form specific attributes (like `action`, `enctype`, etc).


The form object has an "invoke" option which allows you to quickly retrieve a form by its *name*. You can use it 
like this:

```php
// Create a new form
$form = new Form();

// Create a field in the form
$form -> textField('name');

// Retrieve the form by it's name using the shorthand:
$field = $form('name');

// ... Etc
```

Fields
------

In short, you have these fields available. 

  * textField(`$name`) 
  * hiddenField(`$name`)
  * passField(`$name`)
  * selectField(`$name`)
  * radioButton(`$name`, `$value = null`)
  * selectField(`$name`)
  * textarea(`$name`, `$cols = 40`, `$rows = 7`)
  * checkBox(`$name`, `$value = 1`)
  * uploadField(`$name`)
  
You also have these buttons available:

  * submitButton(`$name`, `$value = ''`)
  * imageButton(`$name`, `$src = ''`)

#### TextField

This will create a new text field. The default type will be `text`, but you can also change it to another type which
are available in HTML5.
```php
textField( $name )
```

To change its type, you can use one of the *TextField::TYPE_* constants. Example:
```php
$form -> textField('email') -> setType( TextField::TYPE_EMAIL );
```



Buttons
------

You can also render buttons with FormHandler. A button is not used for validation, 
but when you create a button we do expect the button to be present in the submitted form.

If you have created mutiple buttons, then only 1 button needs to be present. If this is not 
the case, we do not handle the form as submitted.
 
Translations
------ 
FormHandler does only contain some default error messages which are shown when a field is incorrect. For each
```Validator``` you can overwrite this error message with a localized variant of the error message.

Only the ```UploadValidator``` and ```ImageUploadValidator``` contain some more translations, based on the reason
why a file could not be uploaded. You can overwrite these also. See for more details the documentation for the 
```UploadValidator``` and ```ImageUploadValidator```.