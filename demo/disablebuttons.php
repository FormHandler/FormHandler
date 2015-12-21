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
\FormHandler\Configuration::set('default_disable_submit_btn', true);

$form = new FormHandler();

for($i = 1; $i <= 15; $i++)
{
    \FormHandler\Field\Text::set($form, 'Field '. $i, 'field_'. $i)
        ->setDefaultValue('Value '. $i);
}

\FormHandler\Button\Submit::set($form, 'Submit 1')
    ->onClick(function()
    {
        echo 'Submit 1 clicked';
    });
\FormHandler\Button\Submit::set($form, 'Submit 2')
    ->onClick(function()
    {
        echo 'Submit 2 clicked';
    });

$f = $form->flush(true);
echo '<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>' . $f;