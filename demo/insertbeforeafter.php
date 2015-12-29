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

include '../src/Loader.php';

use \FormHandler\FormHandler;
use \FormHandler\Field as Field;
use \FormHandler\Button as Button;
use \FormHandler\Validator as Validator;

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

$form = new FormHandler();

for($i = 1; $i <= 15; $i++)
{
    Field\Text::set($form, 'Field '. $i, 'field_'. $i);
}

Field\Text::set($form, 'Insert before 5', 'before_five')
    ->insertBefore('field_5');

Field\Text::set($form, 'Insert after 6', 'after_six')
    ->insertAfter('field_6');

Field\Text::set($form, 'Move before 1', 'before_1');

$form->moveFieldBefore('field_1', 'before_1');

Field\Text::set($form, 'Move after 15', 'after_15');

$form->moveFieldAfter('field_15', 'after_15');

//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Test for moving fields after definition<hr>';
echo $form_html;