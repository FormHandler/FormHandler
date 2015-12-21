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

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

$form = new FormHandler();

\FormHandler\Button\Button::set($form, 'Disable Button')
    ->setDisabled();

\FormHandler\Button\Button::set($form, 'Disable and enable Button')
    ->setDisabled()
    ->setDisabled(false);

\FormHandler\Button\Cancel::set($form, 'Disable CancelButton')
    ->setDisabled();

\FormHandler\Button\Reset::set($form, 'Disable ResetButton')
    ->setDisabled();

$var = $form->flush(true);

echo 'Test for button confirmation';

echo '<hr><script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>';

echo $var;