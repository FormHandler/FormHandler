function changeValue( field, set)
{
    // the fields
    var FromField  = $('#'+  field + (set?"_ListOff":"_ListOn") ),
        ToField    = $('#'+   field + (set?"_ListOn":"_ListOff") ),
        SelField   = $('#'+   field + "_ListOn"),
        ValueField = $('#'+   field );

    //remove empty options
    ToField.children("option").each(function()
    {
        if($.trim($(this).val()) == '')
            $(this).remove();
    });

    //move fields and focus correctly
    FromField.children('option').each(function()
    {
        var v = $(this);
        if(v.is(':selected'))
        {
            v.remove().appendTo(ToField);
        }
    });

    //set hidden value
    ValueField.val('__FH_JSON__' + JSON.stringify(SelField.find('option').map(function(){return this.value;}).get()));

    //set focus and selected values properly
    FromField.get(0).focus();
    FromField.get(0).selectedIndex = -1;
    ToField.get(0).selectedIndex = -1;

    return false;
}

// function to move all values..
function moveAll( field, set )
{
    $('#'+ field + (set?"_ListOff":"_ListOn") ).find('option').attr('selected','selected');
    changeValue( field, set );
}