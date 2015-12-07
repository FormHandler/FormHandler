function FH_VALIDATE( validator, field, error, path, includedir, msg )
{
	var url = path + '/ajax/validate.php';
	var pars = 'value=' + $('#'+field).val();
	pars += '&field=' + field;
	pars += '&validator=' + validator;
	pars += '&includedir=' + includedir;
	pars += '&msg=' + escape( msg );	
	pars += '&msgbox=error_' + error;
	var target = '#error_'+ error;
	$(target).load(url,pars);
}