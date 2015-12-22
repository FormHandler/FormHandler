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

Field\ColorPicker::set($form, 'Color', 'color');

//button for submitting
Button\Submit::set($form, 'Send');

//set the 'commit-after-form' function
$form->onCorrect(function($data)
{
    return "Hello " . $data['name'] . ", you picked the color " . $data['color'] . "!";
});

//display the form
$var = $form->flush();

echo 'Basic FormHandler demo<hr><script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>';

echo $var;
