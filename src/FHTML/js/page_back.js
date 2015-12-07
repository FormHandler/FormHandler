// go a page back when a multi-paged form is used
function pageBack(frm) {
    fld = frm.elements[frm.id+'_page'];
    fld.value = fld.value - 2;
    frm.submit();
}