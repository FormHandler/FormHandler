
// function to check the extension of the upload-file
function fh_checkUpload(elem, ext, message) 
{
    var types = ext.split(' ');
    var fp = elem.value.split('.');
    var extension = fp[fp.length-1].toLowerCase();
    for(var i = 0; i < types.length; i++ ) {
        if(types[i] == extension) return true;
    }
    message = message.replace('%s', ext);
    alert(message);
    elem.parentNode.innerHTML = elem.parentNode.innerHTML;
    return false;
}
