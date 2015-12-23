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
 * @author Ruben de Vos
 */

include '../src/Loader.php';

use \FormHandler\FormHandler;
use \FormHandler\Field as Field;
use \FormHandler\Button as Button;

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

//create a new FormHandler object
$form = new FormHandler();

//some fields.. (see manual for examples)
Field\Text::set($form, 'Name', 'name', FH_STRING)
    ->setMaxlength(40);

Field\Number::set($form, 'Age', 'age', FH_INTEGER)
    ->setMin(1)
    ->setMax(110)
    ->setStep(1);

//button for submitting
Button\Submit::set($form, 'Send');

//set the 'commit-after-form' function
$form->onCorrect(function($data)
{
    return "Hello " . $data['name'] . ", you are " . $data['age'] . " years old!";
});

//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Basic FormHandler demo<hr>';
echo $form_html;
