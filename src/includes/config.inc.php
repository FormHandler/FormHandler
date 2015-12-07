<?php
/**
 * config.inc.php
 *
 *
 * The file contains the configuration options used by the FormHandler.
 * You can change the values below. It's also possible to define your own
 * values before you create a new FormHandler object:
 *
 * define('FH_TIMEFIELD_SET_CUR_TIME', false);
 *
 * $form = new FormHandler();
 *
 * ....
 *
 * At this way you dont have to change the config.inc.php to change a
 * specific configuration value once.
 *
 * @author Teye Heimans
 * @package FormHandler
 */

// Get the location where formhandler is located
if( !empty($_SERVER['DOCUMENT_ROOT']) )
{
    $__fh_root = str_replace( $_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', realpath( dirname(__FILE__).'/../' ))).'/';
}
// could not fetch the location of the FH3 directory!
else
{
    // Please set here the location to the FH3 directory
    // like: /engineering/FH3/
    $__fh_root = '';

    if( empty( $__fh_root ) )
    {
        trigger_error(
          "Please set the location to the FH3 directory in the config file!!",
          E_USER_WARNING
        );
    }
}

// The default name of the form
// when no name is given in the constructor
fh_conf('FH_DEFAULT_FORM_NAME', 'FormHandler');

// The default mask used to parse the fields
// The mask can be changed with the function setMask()
fh_conf('FH_DEFAULT_ROW_MASK',"  <tr>\n".
  "    <td valign='top' align='right'>%title%</td>\n".
  "    <td valign='top'>%seperator%</td>\n".
  "    <td valign='top'>%field% %help% <span id='%error_id%' class='error'>%error%</span></td>\n".
  "  </tr>\n"
);

// When addLine() is used, this line is used to set the data in
// (%s is going to be replaced with the value!)
fh_conf('FH_LINE_MASK',
  "  <tr><td>&nbsp;</td><td>&nbsp;</td><td>%s</td></tr>\n"
);

// Default for use table, can be overwritten by useTable
//since 08-10-2009 JW
fh_conf( 'FH_USE_TABLE', true );

// Default for Set focus, can be overwritten by setFocus
//since 14-01-2010 JW
fh_conf( 'FH_SET_FOCUS', true );

// When borderStart() and borderEnd() are used, this fieldset is inserted
fh_conf('FH_FIELDSET_MASK',
  "  <tr>\n".
  "    <td valign='top' colspan='3'>\n".
  "      <br />\n".
  "      <fieldset id='%name%' %extra%>\n".
  "      <legend>%caption%</legend>\n".
  "      <!-- content of fieldset %name%  -->\n".
  "      <table cellspacing='0' cellpadding='3' id='%name%'>\n".
  "        %content%\n".
  "      </table>\n".
  "      <!-- end of fieldset %name% -->\n".
  "      </fieldset>\n".
  "    </td>\n".
  "  </tr>\n"
);

// The error mask used to surround the error messages
fh_conf('FH_ERROR_MASK',
  '<span id="error_%s" class="error">%s</span>'
);

// The mask used for the horizontal listfield
fh_conf('FH_LISTFIELD_HORIZONTAL_MASK',
  "  <table border='0' cellspacing='0' cellpadding='0'>\n".
  "    <tr>\n".
  "      <td align='center'><strong>%onlabel%</strong></td>\n".
  "      <td align='center'></td>\n".
  "      <td align='center'><strong>%offlabel%</strong></td>\n".
  "    </tr>\n".
  "    <tr>\n".
  "      <td rowspan='2' align='right'>\n".
  "        %onfield%\n".
  "      </td>\n".
  "      <td width='30' align='center' valign='bottom'>\n".
  "        <input type='button' value=' &gt; ' onclick=\"changeValue('%name%', false)\" ondblclick=\"moveAll('%name%', false)\" title='%ontitle%' />\n".
  "      </td>\n".
  "      <td rowspan='2'>\n".
  "        %offfield%\n".
  "      </td>\n".
  "    </tr>\n".
  "    <tr>\n".
  "      <td align='center' valign='top'>\n".
  "        <input type='button' value=' &lt; ' onclick=\"changeValue('%name%', true)\" ondblclick=\"moveAll('%name%', true)\" title='%offtitle%' />\n".
  "      </td>\n".
  "    </tr>\n".
  "  </table>"
);

// The mask used for the vertical listfield
fh_conf('FH_LISTFIELD_VERTICAL_MASK',
  "  <table border='0' cellspacing='0' cellpadding='0'>\n".
  "    <tr>\n".
  "      <td align='right' valign='middle'><strong>%offlabel%</strong></td>\n".
  "      <td valign='top' align='left'>\n".
  "        %offfield%\n".
  "      </td>\n".
  "    </tr>\n".
  "    <tr>\n".
  "      <td colspan='2' height='30' align='center' valign='middle'>\n".
  "        <input type='button' value=' &darr; ' onclick=\"changeValue('%name%', true)\" ondblclick=\"moveAll('%name%', true)\" title='%offtitle%' />&nbsp;\n".
  "        &nbsp;<input type='button' value=' &uarr; ' onclick=\"changeValue('%name%', false)\" ondblclick=\"moveAll('%name%', false)\" title='%ontitle%' />\n".
  "      </td>\n".
  "    </tr>\n".
  "    <tr>\n".
  "      <td align='right' valign='middle'><strong>%onlabel%</strong></td>\n".
  "      <td valign='top' align='left'>\n".
  "        %onfield%\n".
  "      </td>\n".
  "    </tr>\n".
  "  </table>"
); 

// Should Overlib javascript be included for help messages?
fh_conf('FH_USE_OVERLIB', true); 

// The help mask used to surround the help messages
fh_conf('FH_HELP_MASK',
    '<img src="%helpicon%" border="0" onmouseover="return overlib(\'%helptext%\', DELAY, \'400\', FGCOLOR, \'#CCCCCC\', BGCOLOR, \'#666666\', TEXTCOLOR, \'#666666\', TEXTFONT, \'Verdana\', TEXTSIZE, \'12px\', CELLPAD, 8, BORDER, 1, CAPTION, \'&nbsp;%helptitle%\', CAPTIONSIZE, \'12px\');" onmouseout="return nd();" style="color:333333;cursor:help;" />'
);

// Default table width. When "false", no width will be set
fh_conf('FH_DEFAULT_TABLE_WIDTH', false);

// The default border size of the table where the form will be located in
fh_conf('FH_DEFAULT_TABLE_BORDER', 0);

// The default cellspacing of the table where the form will be located in
fh_conf('FH_DEFAULT_TABLE_CELLSPACING', 0);

// The default cellpadding of the table where the form will be located in
fh_conf('FH_DEFAULT_TABLE_CELLPADDING', 3);

// The default number of caracters used in the captcha
fh_conf( 'FH_CAPTCHA_LENGTH',6 );
// The width of a captcha image
fh_conf( 'FH_CAPTCHA_WIDTH',200 );


// Does formhandler has to detect the language atomatically?
fh_conf('FH_AUTO_DETECT_LANGUAGE', true);

// If no correct language could be found or auto detect language is disabled,
// what should be the default language ?
fh_conf('FH_DEFAULT_LANGUAGE', 'en');

// When an unknown record is tried to edit, should FormHandler insert
// the record instead ?
fh_conf('FH_AUTO_INSERT', false);

// The URL to web root where the FHTML dir is located (So not the path!). The URL can also be relative to the web root.
// Example: fh_conf('FH_FHTML_DIR', 'http://www.mysite.com/dir/to/FHTML/');
// Or:      fh_conf('FH_FHTML_DIR', '/dir/to/FHTML/');

// here we try to get the dir automatically
fh_conf('FH_FHTML_DIR', $__fh_root . 'FHTML/' );

// This config var has to point to the FCKeditor directory.
// Default this dir is located in the FH3 directory. If you put it
// somewhere else you have to change this config var.
// added 21-11-2008 By Johan Wiegel in order to make it posible to place FH3 outside webroot and FHMTL directory inside webroot
fh_conf('FH_FHTML_INCLUDE_DIR', FH_INCLUDE_DIR . 'FHTML/');

// mask for a TextSelectField
fh_conf( 'FH_TEXTSELECT_MASK',
  '<input type="text" name="%s" id="%1$s" value="%s" size="%d" %s onblur="FH_CLOSE_TEXTSELECT(\'FHSpan_%1$s\');" onkeyup="FH_CLOSE_TEXTSELECT(\'FHSpan_%1$s\');" onclick="document.getElementById(\'FHSpan_%1$s\').style.display=\'block\';" style="background: #FFF url('.$__fh_root . 'FHTML/images/arrow_down.gif) no-repeat right;" />%s<br /><div style="position:absolute;height:70px;overflow-y:scroll; width:150px; display:none; background-color:#FFF;" id="FHSpan_%1$s">%s</div>'."\n"
);

// mask for the TextSelectField options
fh_conf( 'FH_TEXTSELECT_OPTION_MASK',
  '<a style="display:block;padding-left:5px;margin:0px;width:auto;color:black;text-decoration:none;" href="#" onmouseover="this.style.background=\'#C0C0C0\';" onmouseout="this.style.background=\'#FFFFFF\';" onblur="FH_CLOSE_TEXTSELECT(\'FHSpan_%s\')" onfocus="FH_SET_TEXTSELECT( \'%1$s\', this.innerHTML );" onclick="FH_SET_TEXTSELECT( \'%1$s\',this.innerHTML );" >%s</a>'."\n"
);

// This config var has to point to the YADAL directory.
// Yadal is the database abstraction class which FormHandler uses
// to interact with the databases.
// Default this dir is located in the FH3 directory. If you put it
// somewhere else you have to change this config var.
fh_conf('FH_YADAL_DIR', FH_INCLUDE_DIR . 'yadal/');

// The default database type which is used when no one is given.
fh_conf('FH_DEFAULT_DB_TYPE', 'mysql');

// The default host which is used when no one is given
fh_conf('FH_DEFAULT_DB_HOST', 'localhost');

// The id which we are watching in the URL of the form is an edit form.
// like: index.php?id=1
//                  ^
//                  |
// or when multiple primary key's are used:
// like: index.php?id[]=1&id[]=en
//                  ^      ^
//                  |      |
fh_conf('FH_EDIT_NAME', 'id');

// In the fields CheckBox, SelectBox and RadioButton it is possible
// to give a array as value. The index (key) of this array can be used
// as value for the field.
// This can be changed in the call of the function:
// $form->CheckBox('title', 'name', array(...), true);
//                                                ^
// You can change the default value of this option...
// Should by default the array key be used as value ??
fh_conf('FH_DEFAULT_USEARRAYKEY', true);


// The default upload config. It contains this config values:
// - where to upload the file,
// - what kind of files are allowed,
// - what the maximum allowed size is of the uploaded file,
// - then new name of the uploaded file,
// - if the field is required (so that visitors must upload a file),
// - what to do if the uploaded file already exists.
// - the allowed dimensions of the uploaded image
fh_conf('FH_DEFAULT_UPLOAD_CONFIG',
  serialize(array (
    'path'     => realpath( '.' ).'/uploads', // <-- dir where the requested script is located
    'type'     => 'jpg jpeg png gif doc txt bmp tif tiff pdf',
    'mime'     => '', // <-- use the mime types which are known by FH for these extensions
    'size'     => '', // <-- max upload size
    'name'     => '', // <-- keep the original name
    'width'    => '', // <-- all widths are permitted! (only used for images)
    'height'   => '', // <-- all heights are permitted! (only used for images)
    'required' => false,
    'exists'   => 'alert' // possible values: alert, overwrite, rename
  ))
);

// Do we have to check the upload file with JS?
// (recomended!)
fh_conf('FH_UPLOAD_JS_CHECK', true);

// The default resize width of the thumbnail
// created by the function resizeImage()
fh_conf('FH_DEFAULT_RESIZE_WIDTH', 180);

// The quality of the thumbnail created by the function resizeImage()
fh_conf('FH_DEFAULT_RESIZE_QUALITY', 80);

// The minimum password length used by the checkPassword() function
fh_conf('FH_MIN_PASSWORD_LENGTH', 5);


// The default hour format used for the time field
fh_conf('FH_TIMEFIELD_DEFAULT_HOUR_FORMAT', 24);

// The steps between the minutes in the timeField
// - Set to 1 for these minutes options: 01, 02, 03, 04, 05, 06, 07, 08, 09, 10, 11, etc...
// - Set to 5 for these minutes options: 00, 05, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55
// - Set to 10 for these minutes options: 00, 10, 20, 30, 40, 50
// - Set to 15 for these minutes options: 00, 15, 30, 45
// etc...
fh_conf('FH_TIMEFIELD_MINUTE_STEPS', 10);

// When the timeField's value is empty (eg. not posted and insert mode),
// should we display the current time ?
fh_conf('FH_TIMEFIELD_SET_CUR_TIME', true);

// The default option if the timeField is required or not
// (Overwrites the current time option! It makes the fields empty)
fh_conf('FH_TIMEFIELD_DEFAULT_REQUIRED', true);

// The default display of the date fields useage:
// d = selectfield day
// m = selectfield month
// y = selectfield year
// D = textfield day
// M = textfield month
// Y = textfield year
fh_conf('FH_DATEFIELD_DEFAULT_DISPLAY', 'd-m-y');

// When the dateField's value is empty (eg. not posted and insert mode),
// should we display the current date ?
fh_conf('FH_DATEFIELD_SET_CUR_DATE', true);

// The default date interval (the years which are displayed)
// which are displayed in the year section of the dateField
fh_conf('FH_DATEFIELD_DEFAULT_DATE_INTERVAL', '90:0');

// The default option if the dateField is required or not
fh_conf('FH_DATEFIELD_DEFAULT_REQUIRED', true);

// The default display of the date fields useage:
// d = day (2 digits with leading zeros)
// D = day
// m = month (2 digits with leading zeros)
// M = month
// y = year (two digits)
// Y = year (four digits)
fh_conf('FH_DATETEXTFIELD_DEFAULT_DISPLAY', 'd-m-Y');

// Use dropdown in jsCalendarPopup
fh_conf('FH_JSCALENDARPOPUP_USE_DROPDOWN', false);

// start day in jsCalendarPopup
// 0 = sunday ... 6 = saturday
fh_conf('FH_JSCALENDARPOPUP_STARTDAY', 1);

// The default size of the listfield field's
fh_conf('FH_DEFAULT_LISTFIELD_SIZE', 4);

// The default glue which should be used to merge multiple
// checkboxes or radiobuttons
fh_conf('FH_DEFAULT_GLUE_MASK', "%field%<br />\n");

// The chmod which is used when a dir is created
fh_conf('FH_DEFAULT_CHMOD', 0777);

// Display errors of FormHandler (PHP errors, NOT field validation errors!)
fh_conf('FH_DISPLAY_ERRORS', true);

// If this variabele is set to true, FH will
// expose itsself by adding a extra line on the bottom of the form
fh_conf('FH_EXPOSE', true);

// Disable the submit button after submitting the form ?
fh_conf('FH_DEFAULT_DISABLE_SUBMIT_BTN', true);

// use / for valid XHTML, '' for valid HTML
fh_conf('FH_XHTML_CLOSE', '/');

// encoding for htmlentities & htmlspecialchars
fh_conf('FH_HTML_ENCODING', 'UTF-8');
/***********************************/
/*** Don't change anything below ***/
/***********************************/

/**
 * Document::fh_conf()
 *
 * Set the configuration defines if they are
 * not defined by the user yet.
 *
 */
function fh_conf()
{
    static $define = array();

    // is a value set?
    if (func_num_args()==2)
    {
        $define[func_get_arg(0)] = func_get_arg(1);
    }
    // no value is set, we have to define the values!
    else
    {
        // walk all values and define them if they dont exists yet
        foreach ($define as $name => $value)
        {
            if(!defined($name))
            {
                define($name, $value);
            }
        }
        unset( $define );
    }
}

// make sure that array_key_exists exists! :D
if( !function_exists('array_key_exists') )
{
	function array_key_exists($sKey, $aArray)
	{
		return in_array($sKey, array_keys($aArray));
	}
}

// making sure we dont get notices on this in PHP versions < 5.4
if( !defined( 'ENT_HTML401' ) )
{
	define( 'ENT_HTML401', '' );
}

// For PHP version < 4.2.0 missing the array_fill function..
if(!function_exists('array_fill'))
{
    function array_fill($iStart, $iLen, $vValue)
    {
        $aResult = array();
        for ($iCount = $iStart; $iCount < $iLen + $iStart; $iCount++)
        {
            $aResult[$iCount] = $vValue;
        }
        return $aResult;
    }
}

?>