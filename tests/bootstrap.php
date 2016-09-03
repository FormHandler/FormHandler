<?php

namespace FormHandler\Tests {

// make sure to start sessions
    session_start();

    require dirname(__DIR__) . '/vendor/autoload.php';
}

// @codingStandardsIgnoreStart
namespace FormHandler\Validator {

    function filesize($file)
    {
        if ($file == __DIR__ . '/Validator/_tmp/test.pdf') {
            return 542;
        } else  if ($file == __DIR__ . '/Validator/_tmp/avatar.gif') {
            return 543;
        } else {
            return \filesize($file);
        }
    }

    function getimagesize( $image )
    {
        if ($image == __DIR__ . '/Validator/_tmp/avatar.gif') {
            return [
                200, // width
                100, // height
                IMAGETYPE_GIF,
                'height="100" width="200"',

            ];
        } elseif ($image == __DIR__ . '/Validator/_tmp/test.pdf') {
            return false;
        } else {
            return \getimagesize( $image );
        }
    }

    /**
     * Check if a function exists.
     * We override this function so that we can mock that the "getmxrr" function does not exists, so that
     * we can test both cases.
     *
     * @param $func
     * @return bool
     */
    function function_exists($func)
    {
        static $count = 0;

        $exists = \function_exists($func);

        if ($func == 'getmxrr') {
            return ($count++ === 0 || !$exists) ? false : true;
        } else {
            return $exists;
        }
    }

    function getmxrr( $host, $tmp )
    {
        static $count = 0;

        return $count++ === 0 ? false : true;
    }
}