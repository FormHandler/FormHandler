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

Field\File::set($form, 'Upload file', 'upload_file')
    ->setDropZoneEnabled(true, 'Drop your custom file here');

Field\Text::set($form, 'Text field', 'some_other_field')
    ->setValidator(new Validator\String());

$form->_setJS('FormHandler.registerHandlerUploaded(\'upload_file\', function(){ alert(\'File uploaded\'); });', false, true);

Button\Submit::set($form, 'Submit 1');

$form->onCorrect(function($data)
{
    echo '<pre>';
    var_dump($_POST);
    var_dump($data);
    
    if(is_object($data['upload_file']))
    {
        echo "\n". $data['upload_file']->getRealpath();
    }
    
    echo '</pre>';
    return true;
});

$form_html = $form->flush();
echo '<!DOCTYPE html>'
. '<html><head>'
    . '<style>'
    . '.FH_dropzone span.upload'
    . '{'
    . 'display:block;'
    . '}'
    . '.FH_dropzone'
    . '{'
    . 'width:250px;'
    . 'padding:30px;'
    . 'display:inline-block;'
    . 'border:3px dashed #CCC;'
    . '}'
    . '.FH_dropzone.dragover'
    . '{'
    . 'border-color:#000;'
    . 'background-color:#EEE;'
    . '}'
    . '</style>'
    . '</head><body>'
    . 'Test for upload field<hr>'
    . $form_html
    .'</body></html>';