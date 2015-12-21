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

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

echo 'Test for multi link view mode';

echo '<hr>';

$form = new FormHandler();

\FormHandler\Field\Text::set($form, 'Single value', 'field1')
    ->setViewMode()
    ->setValue('VALUE')
    ->setViewModeLink('startofurl?value={$value}&extra=1');

\FormHandler\Field\Select::set($form, 'Multi value', 'field2')
    ->setViewMode()
    ->setValue(array(1,2))
    ->setOptions(array(1 => 'Value 1', 2 => 'Value 2', 3 => 'Value 3'))
    ->setViewModeLink('startofurl?value={$value}&extra=1');

$form->flush();