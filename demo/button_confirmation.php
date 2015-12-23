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

$form = new FormHandler();

$form->addLine('Button with confirmation on click', true);
Button\Button::set($form, 'Click me!', 'btn_1')
    ->setConfirmation('Do you really want to click this button?');

$form->addLine('<br>Button with confirmation and description on click', true);
Button\Button::set($form, 'Click me!', 'btn_2')
    ->setConfirmation('Do you really want to click this button?')
    ->setConfirmationDescription('And some extra description');

$form->addLine('<br>Button with confirmation, description and alert after confirmation approved', true);
Button\Button::set($form, 'Click me!', 'btn_3')
    ->setConfirmation('Do you really want to click this button?')
    ->setConfirmationDescription('And some extra description')
    ->setExtra('onclick="alert(\'This alert should be displayed after confirmation is success\');"');


//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Test for button confirmation<hr>';
echo $form_html;