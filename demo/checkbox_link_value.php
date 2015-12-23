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

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

$form = new FormHandler();

Field\CheckBox::set($form, '', 'inherit')
    ->setOptions(array(1 => 'Inherit from parent, integer value'));

Field\CheckBox::set($form, '', 'inherit1')
    ->setOptions(array(1 => 'Linked to above'));

Field\CheckBox::set($form, '', 'inherit3')
    ->setOptions(array('1' => 'Linked to first checkbox, string value'))
    ->setDisabled();

Field\CheckBox::set($form, 'Multi values<br>', 'inherit4')
    ->setOptions(array(
        1 => 'Value 1',
        2 => 'Value 2',
        3 => 'Value 3',
    ))
    ->setValue(2, true, true);

Field\Text::set($form, 'Linked to first checkbox', 'inherit2');

$link = function($v)
{
    $value = !empty($v);
    return \FormHandler\FormHandler::returnDynamic(array($value), null, null, null, 'checkbox');
};

$form->link('inherit', 'inherit1', $link);
$form->link('inherit', 'inherit3', $link);
$form->link('inherit', 'inherit4', $link);

$form->link('inherit', 'inherit2', function($v)
{
    return \FormHandler\FormHandler::returnDynamic(json_encode($v), null, null, null, 'text');
});

//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Test for linking checkboxes<hr>';
echo $form_html;