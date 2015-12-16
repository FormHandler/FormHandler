# FormHandler

FormHandler is a PHP written "module" which allows you to create dynamic forms in an easy way.
So easy that you can build a fully working form, including field validations, within 10 lines!

## Installation

Install the latest version with

```bash
$ composer require formhandler/formhandler
```

## Basic Usage

```php
<?php

//include the class (only needed when not using Composer)
include "FH3/class.FormHandler.php";

//create a new FormHandler object
$form = new FormHandler();

//some fields.. (see manual for examples)
$form->textField("Name", "name", FH_STRING, 20, 40);
$form->textField("Age", "age", FH_INTEGER, 4, 2);

//button for submitting
$form->submitButton();

//set the 'commit-after-form' function
$form->onCorrect('doRun');

//display the form
$form->flush();

//the 'commit-after-form' function
function doRun($data)
{
    echo "Hello ". $data['name'].", you are ".$data['age'] ." years old!";
}
```

### Documentation

For an extended version of the documentation please check our manual located at
http://www.formhandler.net/manual/manual.html

### License

FormHandler is licensed under the GNU Lesser General Public License Version 2.1 - see the `LICENSE` file for details
