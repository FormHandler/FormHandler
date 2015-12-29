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
    Field\Text::set($form, 'Field '. $i, 'field_'. $i)
        ->setValue('Value ' . $i);
}

$form->getField('field_1')->hideFromOnCorrect();
$form->getField('field_3')->hideFromOnCorrect();
$form->getField('field_5')->hideFromOnCorrect();
$form->getField('field_7')->hideFromOnCorrect();

$form->onCorrect(function($data)
{
    echo '<pre>Field 1, 3, 5, 7 should be hidden from results'."\n";
    print_r($data);
    echo '</pre>';
    exit;
});

Button\Submit::set($form, 'Press submit to see if the oncorrect data works');

//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Test for hiding fields from the onCorrect function<hr>';
echo $form_html;