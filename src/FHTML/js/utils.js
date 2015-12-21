/**
 * Document
 *
 * @author marien
 * @copyright ColorBase™
 */

(function(FormHandler,$,undefined)
{
    FormHandler.getValue = function($field,to_server)
    {
        if($field.is(':radio'))
        {
            return $field.filter(':checked').val();
        }
        if($field.is(':checkbox'))
        {
            var val = [];
            $.each($field, function(key,el)
            {
                if(el.checked)
                {
                    val.push(String($(el).val()));
                }
            });

            if(typeof to_server !== 'undefined'
                && to_server === true)
            {
                val = '__FH_JSON__'+ JSON.stringify(val,null,2);
            }

            return val;
        }

        return $field.val();
    };
}(window.FormHandler = window.FormHandler || {}, jQuery));