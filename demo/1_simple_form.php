<?php

use FormHandler\Form;

require dirname(__DIR__) . '/vendor/autoload.php';

$form = new Form();
$form->textField('name');
$form->submitButton('btn', 'Submit');

if ($form->isSubmitted($reason)) {
    if ($form->isValid()) {
        printf("Hello <b>%s</b><br />",
            htmlentities(
                $form('name')->getValue() ?: 'John Doe'
            )
        );
    } else {
        print_r($form->getValidationErrors());
    }
}

//var_dump( $reason, $form -> isSubmitted(), $form -> isValid() );

echo $form;
echo "My Field:\n";
echo $form('name');
echo $form('btn');




