function trim(value) {
  value = value.replace(/^\s+/,'');
  value = value.replace(/\s+$/,'');
  return value;
}

function changeValue( field, set) {

    // the fields
    FromField  = document.getElementById( field + (set?"_ListOff":"_ListOn") );
    ToField    = document.getElementById( field + (set?"_ListOn":"_ListOff") );
    SelField   = document.getElementById( field + "_ListOn");
    ValueField = document.getElementById( field );
    
    // remove empty value from the from and to field ( <option /> tag's)
    for( i = 0; i < ToField.options.length; i++ ) {
        if( trim(ToField.options[i].text) == "" && trim(ToField.options[i].value) == "") {
            ToField.remove(i);
        }
    }              

    // is something selected ?
    while(FromField.value != "") {
        // get the number of options of the "new" field
        var len = ToField.options.length;

        // add the option
        ToField.options[len] = new Option(FromField.options[FromField.selectedIndex].text);
        ToField.options[len].value = FromField.options[FromField.selectedIndex].value;
        
        // remove the option from the old list
        FromField.options[FromField.selectedIndex] = null;        
    }
    
    // set the focus and select the top item if there is one.
    FromField.focus();
    if( FromField.options.length > 0 ) {
        FromField.options[0].selected = true;
    }

    // put the selected value's in the hidden field
    SelectedVars = new Array();
    for(i = 0; i < SelField.options.length; i++)
      SelectedVars[i] = SelField.options[i].value;

    ValueField.value = SelectedVars.join(", ");
}

// function to move all values..
function moveAll( field, set ) {
    FromField  = document.getElementById( field + (set?"_ListOff":"_ListOn") );

    for( i = 0; i < FromField.options.length; i++ ) {
        FromField.options[i].selected = true;
    }
    changeValue( field, set );
}