/**
 * Document
 *  
 * @author marien
 * @copyright ColorBase™
 */

(function(FormHandler,$,undefined)
{
    var appearanceData = {};
    
    FormHandler.appearanceWatch = function(formName, fieldsToWatch, forField)
    {
        if(appearanceData.hasOwnProperty(formName) === false)
        {
            appearanceData[formName] = {connected:[],watches:[]};
        }
        
        $.each(fieldsToWatch,function(watch_key, value)
        {
            var field = $(value[0]);
            
            if(field.length === 0)
            {
                //continue to next item
                return;
            }
            
            if(appearanceData[formName].connected.hasOwnProperty(watch_key) === false)
            {
                appearanceData[formName].connected[watch_key] = [];
                
                field
                    .data('watch', watch_key)
                    .data('formName', formName)
                    .change(function()
                    {
                        var el = $(this),
                            wk = el.data('watch'),
                            fn = el.data('formName'),
                            values = {};
                        
                        $.each(appearanceData[fn].connected[wk], function(k,v)
                        {
                            var wf = appearanceData[fn].watches[v];
                            
                            values[v] = {};
                            
                            $.each(wf, function(delayed_key, delayed_value)
                            {
                                //when field is not found, pick value from values array
                                var find = $(delayed_value[0]);
                                values[v][delayed_key] = (find.length === 0) 
                                    ? delayed_value[1] 
                                    : FormHandler.getValue(find,true); 
                            });
                        });

                        $.ajax(document.location.href, {
                            type: 'POST',
                            data: 'appearance=true&values='+ JSON.stringify(values) 
                                    +'&form_name='+ formName,
                            cache: false,
                            success: function(data)
                            {
                                if(typeof data !== 'object')
                                {
                                    return;
                                }
                                
                                $.each(data, function(key, value)
                                {
                                    var $forField = $('#'+ key + '_field');
                                    if(value === true || value === false)
                                    {
                                        $forField.css('display',(value === true ? 'block' : 'none'));
                                    }
                                });
                            }
                        });
                    });
            }
            
            if($.inArray(forField, appearanceData[formName].connected[watch_key]) === -1)
            {
                appearanceData[formName].connected[watch_key].push(forField);
            }
            
            appearanceData[formName].watches[forField] = fieldsToWatch;
        });
    };
}(window.FormHandler = window.FormHandler || {}, jQuery));