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

\FormHandler\Field\CheckBox::set($form, '', 'inherit')
    ->setOptions(array(
        1 => 'Normal option',
        2 => 'Force value',
        3 => 'Normal option 2'
    ))
    ->setValue(1)
    ->setValue(2, true, true)
    ->setValue(3, false, true)
    ->setDisabled(3);

\FormHandler\Button\Submit::set($form, 'Submit');

$form->onCorrect(function(){ return false; });

$var = $form->flush(true);

echo 'Test for forcing values on checkboxes';

echo '<hr><script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>';

echo $var;