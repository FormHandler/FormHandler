<?php

/**
 * @author Ruben de Vos <ruben@color-base.com>
 * @copyright ColorBase™
 */

include '../src/FormHandler.php';

$form = new FormHandler();

$form->addLine('Fill captcha', true);

$form->CaptchaField('Required captcha', 'captcha');

$form->onCorrect(function($data)
{
    echo 'Captacha field working!';
});

SubmitButton::set($form);

$var = $form->flush(true);

echo 'Test captacha field';

echo '<hr><script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>';

echo $var;