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

//create a new FormHandler object
$form = new FormHandler();

//some fields.. (see manual for examples)
Field\Date::set($form, 'Date', 'date')->setRequired(true)->setValidator(new Validator\Date());
Field\Date::set($form, 'Date with min', 'date-min')->setRequired(true)->setMinDate('2017-01-01');
Field\Date::set($form, 'Date with max', 'date-max')->setRequired(true)->setMaxDate('2017-01-01');
Field\Date::set($form, 'Date with both', 'date-both')->setMinDate('2017-01-01')->setMaxDate('2017-01-03');

//button for submitting
Button\Submit::set($form, 'Send');

//set the 'commit-after-form' function
$form->onCorrect(function($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit();
});

//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Basic FormHandler demo<hr>';
echo $form_html;
