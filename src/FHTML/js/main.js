/**
 * Document
 *
 * @author marien
 * @copyright ColorBase™
 */
String.prototype.htmlEntitiesEncode = function()
{
    var replace_map;

    replace_map = {
        '&' : '&amp;',
        '<' : '&lt;',
        '>' : '&gt;',
        '"' : '&quot;',
        "'": '&#x27;',
        '/' : '&#x2F;',
    };

    return this.replace(/[&<>"'/"]/g, function(match)
    {
        return typeof replace_map[match] !== 'undefined'
            ? replace_map[match]
            : match;
    });
};

String.prototype.htmlEntitiesDecode = function()
{
    var replace_map;

    replace_map = {
        '&amp;' : '&',
        '&lt;' : '<',
        '&gt;' : '>',
        '&quot;' : '"',
        '&#x27;' :  "'",
        '&#x2F;' : '/',
    };

    return this.replace(/(&amp;|&lt;|&gt;|&quot;|&#x27;|&#x2F;)/g, function(match)
    {
        return typeof replace_map[match] !== 'undefined'
            ? replace_map[match]
            : match;
    });
};

(function(FormHandler,$,undefined)
{
    var handlers = [];

    FormHandler.config = [];

    FormHandler.registerHandler = function(callback)
    {
        handlers.push(callback);
    };

    FormHandler.truncateString = function(n, lngth)
    {
        lngth = (lngth === undefined) ? 30 : lngth;
        var chunk = (lngth-2 > 2) ? Math.round((lngth-2)/2) : 0;
        if(n.length-2 > lngth
                && chunk > 2)
        {
            var start = n.substring(0, chunk);
            var end = n.substring(n.length-chunk, n.length);
            n = start + '...' + end;
        }
        return n;
    };

    var confirmationHandler = function (message, description, callable, options)
    {
        message = (description === undefined ? message : message + "\n\n" + description);

        if(confirm(message))
        {
            if(typeof callable === "function")
            {
                callable(options);
            }

            return true;
        }
        return false;
    };

    FormHandler.registerConfirmationHandler = function (handlerMethod)
    {
        if(typeof handlerMethod === 'function')
        {
            confirmationHandler = handlerMethod;
        }
    };

    //disable buttons functionality
    FormHandler.registerHandler(function(Form)
    {
        //disabled buttons don't send their name,
        //so on click we add a hidden field with the same name
        Form.dom.find('button[type=submit][data-disable=1]').each(function()
        {
            var btn = $(this);
            btn.on('click', function()
            {
                Form.dom.append('<input type="hidden" name="'+ btn.attr('name') +'" data-used-button="1">');
            });
        });

        //process disabling of the buttons
        Form.dom.on('submit', function()
        {
            Form.disableButtons(true);
        });
    });

    $(document).ready(function()
    {
        $("form[data-fh='true']").each(function()
        {
            var active_form = $(this),
                Form = {
                    id: active_form.attr('id'),
                    action: active_form.attr('action'),
                    acceptCharset: active_form.attr('accept-charset'),
                    dom: active_form,
                    submit: function()
                    {
                        Form.dom.submit();
                    },
                    dialog: function(message, description, currentOnclick)
                    {
                        //get handler from FormHandler configuration or get default
                        var handler = FormHandler.config.confirmationHandler || confirmationHandler,
                            callable = function(options)
                            {
                                //check post confirmation handler is availale
                                var onClick = options.currentOnclick || false;

                                //execute when available
                                if(typeof onClick === "function")
                                {
                                    onClick();
                                }
                            },
                            options = [];

                        //add current onclick to callable options when valid function
                        if(typeof currentOnclick === "function")
                        {
                            options.currentOnclick = currentOnclick;
                        }

                        //execute confirmation handler
                        handler(message, description, callable, options);
                    },
                    disableButtons: function(boolean)
                    {
                        active_form.find("button[data-disable=1]").each(function()
                        {
                            if(boolean)
                            {
                                //disable button
                                $(this).attr('disabled','disabled');
                                return;
                            }
                            $(this).removeAttr('disabled');
                            active_form.find('input[data-used-button=1]').remove();
                        });
                    }
                };

            //scan buttons and register confirmation handler
            Form.dom.find('button[data-confirmation]').each(function()
            {
                var btn = $(this),
                    message = btn.data('confirmation'),
                    description = btn.data('confirmation-description'),
                    currentOnclick = btn.prop('onclick'),
                    btnIsSubmit = btn.prop('type') === 'submit';

                //remove current onclick attribute and unbind click handler when available
                if(typeof currentOnclick === 'function')
                {
                    btn.removeAttr('onclick').unbind('click');
                }
                else if(btnIsSubmit)
                {
                    currentOnclick = function()
                    {
                        Form.submit();
                    };
                }

                //reregister onclick function with FormHandler implementation
                btn.on('click', function(event)
                {
                    //call current form dialog function
                    Form.dialog(message, description, currentOnclick);

                    //do not process button default click
                    event.stopPropagation();
                    return false;
                });

                //cleanup button
                btn.removeAttr('data-confirmation').removeAttr('data-confirmation-description');
            });

            $.each(handlers, function(index, callback)
            {
                callback(Form);
            });
        });
    });
}(window.FormHandler = window.FormHandler || {}, jQuery));