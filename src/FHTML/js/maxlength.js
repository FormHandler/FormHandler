// when we could not retrieve the div to display
// the message in, then display a error message only once!
var chacheMsg = new Array();

// function to limit the input of a textarea
function displayLimit( sForm, sField, iMaxLength, bShow, sMessage )
{
    // get the field object
    oFld = document.forms[sForm].elements[sField];

    // get the length of the field
    iLen = oFld.value.length;

    // try to get the div
    var div = document.getElementById ? document.getElementById(sField + '_limit') : document.all? document.all[sField + '_limit']: null;

    // should we display the message ?
    if( bShow )
    {
        // did we got the div ?
        if( div )
        {
            // set the message in the div
            iLeft = iMaxLength - iLen;
            div.innerHTML = sMessage.replace('%d', ( iLeft >= 0 ? iLeft : 0)  );
        }
        // we did not fetch the div correctly
        else
        {
            // check if we have to alert the user
            for( var i = 0; i < chacheMsg.length; i++ )
            {
                if( chacheMsg[i] == sField )
                {
                    // the message has been send before! do nothing!
                    bMessage = false;
                    return;
                }
            }

            // when we come here, the message has not been send yet
            // alert the user
            alert(
              'Error, could not display the number of characters '+
              'you have left for the field "' + sField +'"\n' +
              'Please note that you can post a maximum number of ' + iMaxLength +' characters!'
            );

            // save the field's name so that the error message is not displayed twice
            chacheMsg[chacheMsg.length] = sField;
        }
    }

    // is the lenght greater then the allowed length ?
    if( iLen > iMaxLength )
    {
        // ai, value is too long..
        // remove the last typed character
        oFld.value = oFld.value.substr(0, iMaxLength);
    }
}