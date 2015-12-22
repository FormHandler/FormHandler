<?php

/*
 * Copyright (C) 2015 FormHandler
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 *
 * @author Marien den Besten
 */

session_start();

include '../src/Loader.php';

use \FormHandler\FormHandler;
use \FormHandler\Field as Field;
use \FormHandler\Button as Button;

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

//create a new FormHandler object
$form = new FormHandler();

$form->addLine('Fill captcha', true);

Field\Captcha::set($form, 'Required captcha', 'captcha');

$form->onCorrect(function($data)
{
    echo 'Captcha field working!';
});

Button\Submit::set($form);

$var = $form->flush(true);

echo 'Test captcha field';

echo '<hr><script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>';

echo $var;