<?php

/**
 * Copyright (C) 2016 Ruben de Vos <ruben@color-base.com>.
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
 */

include '../src/Loader.php';

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

$form = new FormHandler\FormHandler();

$field = FormHandler\Field\Percentage::set($form, 'Number', 'length')
    ->allowEmpty(true);

$field->setValue(null);

$field->setEmptyText('FAIL');

$html = $form->flush();


echo 'Test for nubmer field with option to set unknown<hr>';
echo $html;