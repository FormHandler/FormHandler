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
use \FormHandler\Field as Field;
use \FormHandler\Button as Button;

\FormHandler\Configuration::set('fhtml_dir', '../src/FHTML/');

$form = new FormHandler();

$form->addLine('<br>Button with custom confirmation handler', true);
Button\Button::set($form, 'Click me!', 'btn_4')
    ->setConfirmation('Do you really want to click this button?')
    ->setConfirmationDescription('And some extra description')
    ->setExtra('onclick="alert(\'Custom handler successfully executed\')"');

$form->addHTML('<div id="testResult"></div>');


//process all form results, needs to be done before any output has been done
$form_html = $form->flush();

//below is code to show the form

echo 'Test for custom button confirmation handler<hr>
'. $form_html .'
<script>
    $(document).ready(function()
    {
        var div = $("#testResult"),
        confirm = function(message, description, callable, options)
        {
            if(div.hasClass(\'active\'))
            {
                return;
            }

            div.addClass(\'active\');

            var buttonYes = $("<button id=\'buttonYes\' data-value=\'yes\'>Yes</button>"),
                buttonNo = $("<button id=\'buttonNo\' data-value=\'no\'>No</button>");

            div.append("<br><strong>Custom handler</strong><br><br>Message: " + message);

            if(description !== undefined)
            {
                div.append("<br>Description: " + description);
            }

            buttonYes.on(\'click\', function(event)
            {
                callable(options);
            });

            $(document).on(\'click\', \'#buttonYes, #buttonNo\', function(event)
            {
                div.empty().removeClass(\'active\');

                event.stopPropagation();
                return false;
            });


            div.append("<br><br>").append(buttonYes).append(" ").append(buttonNo);
        };

        (function(FormHandler,$,undefined)
        {
            FormHandler.config = {confirmationHandler: confirm};
        }(window.FormHandler = window.FormHandler || {}, $));
    });
</script>';