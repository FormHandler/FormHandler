<?php

namespace FormHandler\Tests {

    // make sure to start sessions
    session_start();

    require dirname(__DIR__) . '/vendor/autoload.php';
    include_once __DIR__ . '/Renderer/FakeElement.php';
    include_once __DIR__ . '/Renderer/BaseTestRenderer.php';
}

// @codingStandardsIgnoreStart
namespace FormHandler\Utils {

    function extension_loaded(string $ext): bool
    {
        if (isset($GLOBALS['mock_extension_not_loaded']) && $GLOBALS['mock_extension_not_loaded'] == $ext) {
            return false;
        } elseif (isset($GLOBALS['mock_extension_loaded']) && $GLOBALS['mock_extension_loaded'] == $ext) {
            return true;
        } else {
            return \extension_loaded($ext);
        }
    }

    function mkdir(string $dirname, int $mode = 0777, bool $recursive = false): bool
    {
        return $GLOBALS['mock_mkdir_response'] ?? \mkdir($dirname, $mode, $recursive);
    }

    function is_writable(string $filename): bool
    {
        return $GLOBALS['mock_is_writable_response'] ?? \is_writable($filename);
    }

    function move_uploaded_file(string $file, string $dest): bool
    {
        return $GLOBALS['mock_move_uploaded_file_response'] ?? rename($file, $dest);
    }

    function gd_info()
    {
        return $GLOBALS['mock_gd_info'] ?? (\function_exists('gd_info') ? \gd_info() : []);
    }

    function function_exists(string $func): bool
    {
        if (isset($GLOBALS['mock_function_exists']) && $GLOBALS['mock_function_exists'] == $func) {
            return true;
        } elseif (isset($GLOBALS['mock_function_not_exists']) && $GLOBALS['mock_function_not_exists'] == $func) {
            return false;
        } else {
            return \function_exists($func);
        }
    }

    function phpinfo(int $int): bool
    {
        if (isset($GLOBALS['mock_php_info'])) {
            echo $GLOBALS['mock_php_info'];

            return true;
        } else {
            return \phpinfo($int);
        }
    }

    function ini_get(string $var)
    {
        if (isset($GLOBALS['mock_ini_get']) && isset($GLOBALS['mock_ini_get'][$var])) {
            return $GLOBALS['mock_ini_get'][$var];
        } else {
            return \ini_get($var);
        }
    }
}

namespace FormHandler\Validator {

    /**
     * @param string $file
     *
     * @return int|false
     */
    function filesize(string $file)
    {
        return $GLOBALS['mock_file_size'] ?? \filesize($file);
    }

    /**
     * @param string $image
     *
     * @return array|false
     */
    function getimagesize(string $image)
    {
        return $GLOBALS['mock_image_size'] ?? \getimagesize($image);
    }

    function function_exists(string $func): bool
    {
        if (isset($GLOBALS['mock_function_exists']) && $GLOBALS['mock_function_exists'] == $func) {
            return true;
        } elseif (isset($GLOBALS['mock_function_not_exists']) && $GLOBALS['mock_function_not_exists'] == $func) {
            return false;
        } else {
            return \function_exists($func);
        }
    }

    /**
     * @param string $host
     * @param array  $tmp
     *
     * @return bool
     */
    function getmxrr(string $host, array &$tmp): bool
    {
        return $GLOBALS['mock_mxrr_response'] ?? \function_exists('getmxrr') && \getmxrr($host, $tmp);
    }

    /**
     * @param string $file
     *
     * @return false|string
     */
    function mime_content_type(string $file)
    {
        return $GLOBALS['mock_mime_content_type'] ?? (\function_exists('mime_content_type') ? \mime_content_type($file) : false);
    }

    function checkdnsrr(string $host, string $type = 'MX'): bool
    {
        return $GLOBALS['mock_dnsrr_response'] ?? \function_exists('checkdnsrr') && \checkdnsrr($host, $type);
    }

    /**
     * @param resource $finfo
     * @param string   $filename
     *
     * @return false|string
     */
    function finfo_file($finfo, string $filename)
    {
        return $GLOBALS['mock_finfo_file'] ?? \finfo_file($finfo, $filename);
    }
}