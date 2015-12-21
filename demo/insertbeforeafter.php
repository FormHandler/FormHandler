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

$form = new FormHandler();

for($i = 1; $i <= 15; $i++)
{
    \FormHandler\Field\Text::set($form, 'Field '. $i, 'field_'. $i);
}

\FormHandler\Field\Text::set($form, 'Insert before 5', 'before_five')
    ->insertBefore('field_5');

\FormHandler\Field\Text::set($form, 'Insert after 6', 'after_six')
    ->insertAfter('field_6');

\FormHandler\Field\Text::set($form, 'Move before 1', 'before_1');

$form->moveFieldBefore('field_1', 'before_1');

\FormHandler\Field\Text::set($form, 'Move after 15', 'after_15');

$form->moveFieldAfter('field_15', 'after_15');

$form->flush();