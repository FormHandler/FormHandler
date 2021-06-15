<?php

use FormHandler\Form;
use FormHandler\Renderer\CowSayRenderer;
use FormHandler\Validator\StringValidator;

require dirname(__DIR__) . '/vendor/autoload.php';

session_start();

$form = new Form('');

$form->setRenderer(new CowSayRenderer());
$form->textField('name')->addValidator(new StringValidator(2, 50, true));

$form->selectField('gender')
    ->addOptionsAsArray(['m' => 'Male', 'f' => 'Female']);

$form->submitButton('submit', 'Submit');

var_dump($form->isSubmitted($reason), $reason);

if ($form->isSubmitted()) {
    if ($form->isValid()) {

    }
}

?>
<html>
    <head></head>
    <body>
        <?= $form ?>
        Enter your name: <?= $form('name'); ?>
        <br/>
        Select your gender: <?= $form('gender') ?>

        <?= $form('submit') ?>

        <?= $form->close() ?>

    </body>
</html>
