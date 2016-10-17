//Formhandler dynamic fields
(function(FormHandler,$,undefined)
{
    //transform field
    function changeField(fld,field_type_new)
    {
        //no changes needed when original is already set
        if((field_type_new === 'select'
                && fld.is('select')) //select
            || (field_type_new === 'text'
                && fld.is('input')
                && fld.attr('type') === 'text') //text
        )
        {
            return fld;
        }

        var translate = {text:'text',integer:'text',date:'hidden',checkbox:'checkbox',password:'password'},
            newObject = (field_type_new === 'select')
                            ? $('<select><option></option></select>')
                            : $('<input type="'+ translate[field_type_new] +'" />');

        newObject = (field_type_new === 'textarea') ? $('<textarea></textarea>') : newObject;

        //make sure field only accepts integers
        if(field_type_new === 'integer')
        {
            newObject.on('keypress',function(event)
            {
                if(document.all)
                {
                    if((event.keyCode < 48 || event.keyCode > 57) && event.keyCode !== 0)
                        return false;
                }
                else
                {
                    if((event.charCode < 48 || event.charCode > 57) && event.charCode !== 0)
                        return false;
                }
                return true;
            });
        }

        if(fld.attr('name') !== undefined) newObject.attr('name',fld.attr('name'));
        if(fld.attr('id') !== undefined) newObject.attr('id',fld.attr('id'));
        if(fld.attr('class') !== undefined) newObject.attr('class',fld.attr('class'));
        newObject.data('field_type',field_type_new);
        fld.replaceWith(newObject);

        if(field_type_new === 'date')
        {
            //when updating please update the date_field in the FH class

            //date placeholder
            var v = (newObject.val() !== "") ? newObject.val() : 'Please select a date',
                date_placeholder = $('<span id="-date-placeholder-'+ newObject.attr('id') +'">'+ v +'</span>');

            newObject.after(date_placeholder); //add to dom
            date_placeholder.after('<button type="button" class="-date-picker-button" id="button-'+ newObject.attr('id') +'">Change</button>');
            DatePickerLoad(newObject.attr('id'));
        }
        else
        {
            //remove date picker fields
            $('#button-'+ newObject.attr('id')).remove();
            $('#-date-placeholder-'+ newObject.attr('id')).remove();
        }

        return newObject;
    }

    // set the new options in the field
    function loadField(oFld,response,wished_value,initial)
    {
        //get field type
        var field_type = (typeof(response.field_type) !== 'undefined') ? response.field_type : null,
            field_name_clean = oFld.attr('name').replace('[]',''),
            value = (typeof(response.value) !== 'undefined') ? response.value : null,
            disabled = (typeof(response.disabled) !== 'undefined') ? response.disabled : null,
            aOptions = (typeof(response.new_options) !== 'undefined') ? response.new_options : null,
            hide = (typeof(response.hide) !== 'undefined') ? response.hide : null,
            old_value = FormHandler.getValue(oFld),
            disabled_original = oFld.attr('disabled') === 'disabled',
            hide_original = $('#'+ field_name_clean +'_field').is(':hidden');

        // remove all current options of the field
        oFld.attr('disabled',false);
        $('#'+ field_name_clean +'_help').css('visibility','visible');
        $('#'+ field_name_clean +'_field').css('display','block');

        if(field_type !== null
            && oFld.length === 1
            && field_type !== 'checkbox'
            && oFld.data('field_type') !== field_type)
        {
            oFld = changeField(oFld,field_type);
        }

        var new_value = (value === null) ? old_value : value;

        if(initial == true
            && value === null
            && typeof wished_value !== 'undefined'
            && wished_value.hasOwnProperty(field_name_clean) === true)
        {
            if(wished_value[field_name_clean] !== null
                && ((typeof wished_value[field_name_clean] === 'object'
                        && wished_value[field_name_clean].length !== 0)
                    || typeof wished_value[field_name_clean] !== 'object'))
            {
                new_value = wished_value[field_name_clean]; //wished value comes hardcoded of the page
            }
        }
        value = new_value;

        var change_value = (field_type !== 'checkbox'
                && value !== null
                && typeof value !== 'object'
                && value !== old_value);

        if(field_type === 'checkbox'
            && value !== null)
        {
            change_value = true;
        }

        if(aOptions !== null
            && field_type === 'select'
            && oFld.length === 1)
        {
            change_value = true;
            oFld.find('option').remove().end();
            oFld.find('optgroup').remove().end();

            // add the new options
            var len = 0;
            var group_start = 0;
            for(var i in aOptions)
            {
                var elem = aOptions[i];

                if(typeof(elem.value) === "string")
                {
                    var ekey = elem.key;
                    var evalue = elem.value;

                    if(ekey.substr(0,7) === '__LABEL')
                    {
                        //create optgroup
                        if(group_start === 1)
                        {
                            oFld.append(group);
                        }

                        var group = $('<optgroup label="'+ evalue +'"></optgroup>');

                        group_start = 1;
                    }
                    else
                    {
                        var option = $('<option value="'+ ekey +'">'+ evalue +'</option>');

                        if(group_start === 1) //put in group
                        {
                            group.append(option);
                        }
                        else //put directly in option
                        {
                            oFld.append(option);
                        }
                    }
                }
                len++;
            }

            if(group_start === 1)
            {
                oFld.append(group);
            }
            oFld[0].selectedIndex = 0;
        }
        else if(field_type === 'checkbox')
        {
            //remove all labels, checkboxes and new lines
            var parentElement = $('#'+field_name_clean + '_field .form_right'),
                currentChildren = parentElement.children('*:not(img.icon_help)'),
                len = 0;

            for(var i in aOptions)
            {
                var elem = aOptions[i];
                if(typeof(elem.value) !== "string")
                {
                    continue;
                }
                len++;

                parentElement.append('<input type="checkbox" name="'+field_name_clean+'[]" id="'+field_name_clean+'_'
                    + len + '" value="'+ elem.key +'"><label for="'+field_name_clean+'_'+ len + '" class="noStyle">'
                    + elem.value + '</label><br>');
            }

            //only remove all old elements when a new element is set, otherwise keep them hidden to keep field avaialble
            //for further update
            if(len !== 0)
            {
                currentChildren.remove()
            }

            oFld = $('input[name="'+ field_name_clean +'[]"]');

            change_value = true;
        }

        if(change_value === true)
        {
            FormHandler.setValue(oFld,value);
        }

        oFld.attr('disabled',disabled_original);

        var trigger = (field_type === 'checkbox') ? 'click' : 'change';
        oFld.triggerHandler(trigger, wished_value, initial);

        if(disabled !== null && disabled === true)
        {
            oFld.attr('disabled',true);

            if(oFld.is(':focus'))
            {
                oFld.blur();
            }

            $('#'+ field_name_clean +'_help').css('visibility','hidden');
        }
        else if(disabled !== null && disabled === false)
        {
            oFld.attr('disabled',false);
        }
        else if(field_type === 'checkbox'
            && typeof disabled === 'object')
        {
            //get all fields with the specific id's and disabled them
            oFld.attr('disabled',false);

            for(i in disabled)
            {
                $('input[name="'+ field_name_clean +'[]"][value="'+disabled[i]+'"]').attr('disabled', true);
            };
        }

        $('#'+ field_name_clean +'_field').css('display',(hide_original === true ? 'none' : 'block'));
        if(hide !== null && hide === true)
        {
            $('#'+ field_name_clean +'_field').css('display','none');
        }
        else if(hide !== null && hide === false)
        {
            $('#'+ field_name_clean +'_field').css('display','block');
        }
    }

    FormHandler.removeErrorState = function($field)
    {
        $field.removeClass('error').parent().find('label').removeClass('error');
        var name = $field.attr('name').replace('[]','');
        $('#error_'+ name).remove();
    };

    FormHandler.setValue = function($field,value)
    {
        if($field.is(':radio'))
        {
            $field.filter('input[value='+ value +']').prop('checked', true);
            return;
        }
        if($field.is(':checkbox')
            || $field.is('[multiple]'))
        {
            var value_new = ($.isArray(value)) ? value : [value],
                value = [];

            $.each(value_new, function(k,v){
                value[String(k)] = String(v);
            });
        }
        else
        {
            value = value + '';
        }

        //make sure that value (or all values in case of multiple selectfield) are available in element options
        if($field.is('select')
            && ($field.prop('multiple') === false || value.length === 1)
            && (value === '' || $('#' + $field.attr('id') + ' option[value=' + value + ']').length  === 0))
        {
            value = $('#' + $field.attr('id') + ' option').val();
        }
        else if($field.is('select, multiple')
                && value.typeof === Array
                && value.length > 1)
        {
            var valueFound = [];
            $(value).each(function(key, optionValue)
            {
                if($('#' + $field.attr('id') + ' option[value=' + optionValue + ']').length  !== 0)
                {
                    valueFound.push(optionValue);
                }
            });

            value = (valueFound.length === 0)
                ? $('#' + $field.attr('id') + ' option').val()
                : valueFound;
        }

        if($field.is(':checkbox'))
        {
            $.each($field,function(i,el)
            {
                var ell = $(el),
                    value_element = String(ell.val());

                ell.prop('checked', ($.inArray(value_element,value) > -1));
            });
            return;
        }
        if($field.is(':file'))
        {
            return;
        }
        $field.val(value);
    };

    FormHandler.load = function(filename, filter, fields, extra, values, $field_from, form_name, from)
    {
        var initial = (values && values.hasOwnProperty('fh_initial') ? 1 : 0);
        if(!initial)
        {
            FormHandler.removeErrorState($field_from);
        }

        if(initial && typeof values === 'object')
        {
            $.each(values,function(key,item)
            {
                if(typeof item === 'string')
                {
                    values[key] = item.htmlEntitiesDecode();
                }
            });
        }

        $.ajax(filename,{
            type: 'POST',
            data: 'linkselect=true&field_from='+ from +'&filter='+ filter +'&fields='+ fields +'&form_name='+ (typeof form_name != 'undefined' ? form_name : 'FH') + ((extra != '') ? '&'+ extra : '') + (typeof values != 'undefined' ? '&initial='+ initial : ''),
            cache: false,
            success: function(data)
            {
                for(var key in data)
                {
                    //only deal with own properties
                    if(!data.hasOwnProperty(key))
                        continue;

                    //load an html buffer
                    if(typeof data[key]['other'] != 'undefined')
                    {
                        $(key).html(data[key]['other']);
                        continue;
                    }

                    var names = [
                        '#'+ form_name +' input[name="'+ key +'"]',
                        '#'+ form_name +' select[name="'+ key +'"]',
                        '#'+ form_name +' textarea[name="'+ key +'"]',
                        '#'+ form_name +' input[name="'+ key +'[]"]',
                        '#'+ form_name +' select[name="'+ key +'[]"]'
                    ];
                    var fld = $(names.join(','));

                    //when checkbox and field not found, create dummy which will be overwritten first
                    //this is a workaround in order to load the field and update the available options
                    if(data[key]['field_type'] === 'checkbox'
                        && fld.length === 0)
                    {
                        var field_div = $('#'+ form_name +' #' + key + '_field .form_right');

                        if(field_div.length !== 0)
                        {
                            field_div.append('<input type="checkbox" name="' + key + '[]">');
                        }
                    }

                    if(fld.length !== 0)
                    {
                        if(!initial)
                        {
                            FormHandler.removeErrorState(fld);
                        }
                        loadField(fld,data[key],values,initial);
                    }
                }
            }
        });
    };
}(window.FormHandler = window.FormHandler || {}, jQuery));