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
use \FormHandler\Validator as Validator;

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

$form = new FormHandler();

$form->addLine('Button with confirmation on click', true);
Button\Submit::set($form, 'Button 1', 'btn_1');
Button\Submit::set($form, 'Button 2', 'btn_2');
Button\Submit::set($form, 'Button 3', 'btn_3');

$form->onCorrect(function($data, FormHandler $form)
{
    return 'Button "'. $form->getButtonClicked() . '" clicked';
});

//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Test for reading out which button has been click in the onCorrect<hr>';
echo $form_html;