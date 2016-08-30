<?php

use FormHandler\Form;

require dirname(__DIR__) . '/vendor/autoload.php';

session_start();

$form = new \FormHandler\Form('');

$form -> textField('name');

$form -> submitButton('submit', 'Submit');
$form -> imageButton('cancel', 'images/cancel.png');

var_dump( $_POST );
?>

<?=$form?>
    Enter your name: <?=$form('name');?>
    <br />

    <?=$form('submit')?>
    <?=$form('cancel')?>

<?=$form->close()?>
