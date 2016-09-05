<?php

namespace FormHandler\Tests {

// make sure to start sessions
    session_start();

    require dirname(__DIR__) . '/vendor/autoload.php';
}

// @codingStandardsIgnoreStart
namespace FormHandler\Utils {
    function extension_loaded($ext)
    {
        if (isset($GLOBALS['mock_extension_not_loaded']) && $GLOBALS['mock_extension_not_loaded'] == $ext) {
            return false;
        } elseif (isset($GLOBALS['mock_extension_loaded']) && $GLOBALS['mock_extension_loaded'] == $ext) {
            return true;
        } else {
            return \extension_loaded($ext);
        }
    }

    function mkdir($dirname, $mode = 0777, $recursive = false)
    {
        if (isset($GLOBALS['mock_mkdir_response'])) {
            return $GLOBALS['mock_mkdir_response'];
        } else {
            return \mkdir($dirname, $mode, $recursive);
        }
    }

    function is_writable($filename)
    {
        if (isset($GLOBALS['mock_is_writable_response'])) {
            return $GLOBALS['mock_is_writable_response'];
        } else {
            return \is_writable($filename);
        }
    }

    function move_uploaded_file($file, $dest)
    {
        if( isset( $GLOBALS['mock_move_uploaded_file_response'] )) {
            return $GLOBALS['mock_move_uploaded_file_response'];
        } else {
            return rename($file, $dest);
        }
    }

    function gd_info()
    {
        if (isset($GLOBALS['mock_gd_info'])) {
            return $GLOBALS['mock_gd_info'];
        } else {
            return \function_exists('gd_info') ? \gd_info() : [];
        }
    }

    function function_exists($func)
    {
        if (isset($GLOBALS['mock_function_exists']) && $GLOBALS['mock_function_exists'] == $func) {
            return true;
        } elseif (isset($GLOBALS['mock_function_not_exists']) && $GLOBALS['mock_function_not_exists'] == $func) {
            return false;
        } else {
            return \function_exists($func);
        }
    }

    function phpinfo($int)
    {
        if (isset($GLOBALS['mock_php_info'])) {
            echo $GLOBALS['mock_php_info'];
            return true;
        } else {
            return \phpinfo($int);
        }
    }

    function ini_get($var)
    {
        if (isset($GLOBALS['mock_ini_get']) && isset($GLOBALS['mock_ini_get'][$var])) {
            return $GLOBALS['mock_ini_get'][$var];
        } else {
            return \ini_get($var);
        }
    }


}

namespace FormHandler\Validator {

    function filesize($file)
    {
        if (isset($GLOBALS['mock_file_size'])) {
            return $GLOBALS['mock_file_size'];
        } else {
            return \filesize($file);
        }
    }

    function getimagesize($image)
    {
        if (isset($GLOBALS['mock_image_size'])) {
            return $GLOBALS['mock_image_size'];
        } else {
            return \getimagesize($image);
        }
    }

    function function_exists($func)
    {
        if (isset($GLOBALS['mock_function_exists']) && $GLOBALS['mock_function_exists'] == $func) {
            return true;
        } elseif (isset($GLOBALS['mock_function_not_exists']) && $GLOBALS['mock_function_not_exists'] == $func) {
            return false;
        } else {
            return \function_exists($func);
        }
    }

    function getmxrr($host, $tmp)
    {
        if (isset($GLOBALS['mock_mxrr_response'])) {
            return $GLOBALS['mock_mxrr_response'];
        } else {
            return \function_exists('getmxrr') ? \getmxrr($host, $tmp) : false;
        }
    }

    function mime_content_type($file)
    {
        if (isset($GLOBALS['mock_mime_content_type'])) {
            return $GLOBALS['mock_mime_content_type'];
        } else {
            return \function_exists('mime_content_type') ? \mime_content_type($file) : false;
        }
    }

    function checkdnsrr($host, $type = 'MX')
    {
        if (isset($GLOBALS['mock_dnsrr_response'])) {
            return $GLOBALS['mock_dnsrr_response'];
        } else {
            return \function_exists('checkdnsrr') ? \checkdnsrr($host, $type) : false;
        }

    }

    function finfo_file($finfo, $filename)
    {
        if (isset($GLOBALS['mock_finfo_file'])) {
            return $GLOBALS['mock_finfo_file'];
        } else {
            return \finfo_file($finfo, $filename);
        }
    }
}