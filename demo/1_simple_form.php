<?php

use FormHandler\Form;

require dirname(__DIR__) . '/vendor/autoload.php';

$form = new Form();
$field = $form -> textField('name');



echo "My Field:\n";
echo $field;




