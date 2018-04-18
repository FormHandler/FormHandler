[![Build Status](https://travis-ci.org/FormHandler/FormHandler.svg?branch=master)](https://travis-ci.org/teyeheimans/FormHandler)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg?style=flat-square)](https://php.net/) 
[![Coverage Status](https://coveralls.io/repos/github/FormHandler/FormHandler/badge.svg?branch=master)](https://coveralls.io/github/teyeheimans/FormHandler?branch=master)

FormHandler
======

This FormHandler is a PHP solution to generate form fields and validate them.
Making forms is in general a time-taking job. In this package we try to offer
a solution so that making forms is easy. 

FormHandler implements the PSR-1 and PSR-2 coding standards. FormHandler implements the PSR-4 autoloading standard.

To create a form you have to:
  * Define the form and its fields
  * Check if the form is submitted and if it's valid
  * Parse the form's fields in your HTML / view
  
Requirements
------

FormHandler requires PHP 5.6 or higher. It should also work on 5.5 and 5.4, but automated Travis testing does 
not work for these versions. 

FormHandler is tested on:
   * PHP 5.6
   * PHP 7.0
   * PHP 7.1
   * PHP 7.2    
   * PHP Latest 'nightly' build

Installation
------

You can install FormHandler by downloading the [latest](https://github.com/teyeheimans/FormHandler/archive/master.zip) 
zip file and include this in your project.
 
We are working on availability for composer.

Usage 
------

FormHandler has a few assumptions:
  - A form is **always** submitted to itself. That means, to the same script/page where the form is defined.
  - We assume that when you create a ```SubmitButton``` or ```ImageButton```, that you don't use 
    own HTML tag buttons.

A very basic example is:
```php
#
# This code defines your form and what to do with it when it's valid.
# This code is probably defined in your controller.
#

// Create the form
$form = new Form();

// Create a field in the form. Fluent method chaining is supported.
$form->textField('name')
    ->addValidator(new StringValidator(2, 50, true, 'You have to supply your name (between 2 and 50 characters)'))
    ->setPlaceholder('Enter your name');

// Check if the form is submitted
if ($form->isSubmitted()) {

    // Check if the form is valid.
    if ($form->isValid()) {
        // Do your stuff here with the form, for example, store something in a database.
    }
} else {
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

So, this was our first basic example. Let's see what happens here. 

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
<?php
$form = new Form();

// An example using method chaining.
$form->textField('name')
     ->setSize(10)
     ->setId('myName')
     ->setTitle('Enter your name')
     ->setPlaceholder('<name here>')
     ->addValidator(new StringValidator(2, 50, true));
```

General methods 
-----

Most objects (the form, fields and the buttons) represent an HTML tag. All of these objects have some [global 
attributes](http://www.w3schools.com/tags/ref_standardattributes.asp) available.
In FormHandler, these global attributes are also available through getters and setters. For example:

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
$form->textField('name');

// Retrieve the form by it's name using the shorthand:
$field = $form('name');

// Or use the "classic" way:
$field = $form->getFieldByName('name');

// ... Etc
```

Fields
------

In short, you have these fields available. 

  * textField(`$name`) 
  * hiddenField(`$name`)
  * passField(`$name`)
  * selectField(`$name`)
  * radioButton(`$name`, `$value = ''`)
  * selectField(`$name`)
  * textarea(`$name`, `$cols = 40`, `$rows = 7`)
  * checkBox(`$name`, `$value = 1`)
  * uploadField(`$name`)
  
Buttons
------

You also have these buttons available:

  * submitButton(`$name`, `$value = ''`)
  * imageButton(`$name`, `$src = ''`)


You can also render buttons with FormHandler. A button is not used for validation, 
but when you create a button we do expect the button to be present in the submitted form.

If you have created mutiple buttons, then only 1 button needs to be present. If this is not 
the case, we assume the form is not submitted.
 
Translations
------ 
FormHandler does only contain some default error messages which are shown when a field is incorrect. For each
```Validator``` you can overwrite this error message with a localized variant of the error message.

Only the ```UploadValidator``` and ```ImageUploadValidator``` contain some more translations, based on the reason
why a file could not be uploaded. You can overwrite these also. See for more details the documentation for the 
```UploadValidator``` and ```ImageUploadValidator```.

HTML Escaping
------

Security is very important in web forms. Displaying non-safe data can create serious security issues. 
Therefore it needs to be absolutly clear how FormHandler displays strings and which data is HTML escaped or not.
 
 There are two rules which you need to remember:
   * The values of fields are **always** escaped, as they can be filled automatically from `$_GET` or `$_POST`. 
   * Other attributes are filled by *you*, and are thus **NOT** escaped. 

This means that button values are thus also not escaped, as they cannot be filled from the `$_GET` or `$_POST`.


Rendering
------
FormHandler tries to limit its functionality to what it should do: handle forms. However, displaying the forms is 
related to this topic. When displaying forms, you cannot limit yourself to only display the form fields. There
are always elements which are related: 

  * Title of the field
  * Label of checkboxes / radio buttons
  * Displaying error messages
  * Displaying "help" information
  * Displaying if the field is required or not

FormHandler tries to not to interfere with the design part of your application. However, it should be clear that 
it's thus inevitable that FormHandler has some responsibility of generating HTML content.

FormHandler comes with a class called a `Renderer`. This class is responsible for rendering the element 
(field/button/form) and all its related information (error messages, titles, etc).
  
A `Renderer` is a simple class which should have at least 1 method: `render( Element $element)`. This method
is in control to generate the correct HTML for the given `Element`. This could be a field, button, form or option.   

The `XhtmlRenderer` is the default renderer. This class will make sure that all elements are rendered as XHTML.
This class will also make sure that:

  * Error messages are rendered as an `<tt>` tag
  * Help messages are rendered as an `<dfn>` tag 
  
You can change this logic to setting it to render them as an attribute, or not render them at all.

Because the `Renderer` is responsible for all rendering, it's quite easy to create your own class which will render 
the elements in the way you expect them. The easiest way of doing this is by extending the XhtmlRenderer. For example 
please take a look at the `CowSayRenderer`, which will render all _Fields_ with a nice CowSay around them.
