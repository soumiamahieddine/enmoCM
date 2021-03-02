<?php

/**
 * Configuration
 *
 * For more info about constants please @see http://php.net/manual/en/function.define.php
 */

/**
 * Configuration for: Error reporting
 * Useful to show every little problem during development, but only show hard errors in production
 */
define('ENVIRONMENT', 'development');

if (ENVIRONMENT == 'development' || ENVIRONMENT == 'dev') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
/*
// the path was given at the end of install with :
// >apt install php-pear php7.3-dev
// >pecl install xdebug
// I keep config here for reference but better to define in php.ini
ini_set( "zend_extension", "/usr/lib/php/20180731/xdebug.so" );

//[debug]
//; Remote settings
//ini_set( "xdebug.remote_autostart", "off        " );
ini_set( "xdebug.remote_enable",    "on         " );
ini_set( "xdebug.remote_handler",   "dbgp       " );
ini_set( "xdebug.remote_mode",      "req        " );
ini_set( "xdebug.remote_host",      "localhost  " );
ini_set( "xdebug.remote_port",      "9000       " );

//; General
ini_set( "xdebug.auto_trace",           "off"                   );
ini_set( "xdebug.collect_includes",     "on"                    );
ini_set( "xdebug.collect_params",       "off"                   );
ini_set( "xdebug.collect_return",       "off"                   );
ini_set( "xdebug.default_enable",       "on"                    );
ini_set( "xdebug.extended_info",        "1"                     );
ini_set( "xdebug.manual_url",           "http://www.php.net"    );
ini_set( "xdebug.show_local_vars",      "0"                     );
ini_set( "xdebug.show_mem_delta",       "0"                     );
ini_set( "xdebug.max_nesting_level",    "100"                   );
ini_set( ";xdebug.idekey",              ""                      );

//; Trace options
ini_set( "xdebug.trace_format",         "0"     );
ini_set( "xdebug.trace_output_dir",     "/tmp"  );
ini_set( "xdebug.trace_options",        "0"     );
ini_set( "xdebug.trace_output_name",    "crc32" );

//; Profiling
ini_set( "xdebug.profiler_append",          "0"     );
ini_set( "xdebug.profiler_enable",          "0"     );
ini_set( "xdebug.profiler_enable_trigger",  "0"     );
ini_set( "xdebug.profiler_output_dir",      "/tmp"  );
ini_set( "xdebug.profiler_output_name",     "crc32" );
*/
}

/**
 * Configuration for: URL
 * Here we auto-detect your applications URL and the potential sub-folder. Works perfectly on most servers and in local
 * development environments (like WAMP, MAMP, etc.). Don't touch this unless you know what you do.
 *
 * URL_PUBLIC_FOLDER:
 * The folder that is visible to public, users will only have access to that folder so nobody can have a look into
 * "/application" or other folder inside your application or call any other .php file than index.php inside "/public".
 *
 * URL_PROTOCOL:
 * The protocol. Don't change unless you know exactly what you do. This defines the protocol part of the URL, in older
 * versions of MINI it was 'http://' for normal HTTP and 'https://' if you have a HTTPS site for sure. Now the
 * protocol-independent '//' is used, which auto-recognized the protocol.
 *
 * URL_DOMAIN:
 * The domain. Don't change unless you know exactly what you do.
 * If your project runs with http and https, change to '//'
 *
 * URL_SUB_FOLDER:
 * The sub-folder. Leave it like it is, even if you don't use a sub-folder (then this will be just "/").
 *
 * URL:
 * The final, auto-detected URL (build via the segments above). If you don't want to use auto-detection,
 * then replace this line with full URL (and sub-folder) and a trailing slash.
 */

define('URL_PUBLIC_FOLDER', '../../../public');
define('URL_PROTOCOL', '//');
define('URL_DOMAIN', $_SERVER['HTTP_HOST']);
define('URL_SUB_FOLDER', str_replace(URL_PUBLIC_FOLDER, '', dirname($_SERVER['SCRIPT_NAME'])));
define('URL', URL_PROTOCOL . URL_DOMAIN . URL_SUB_FOLDER);

/**
 * Configuration for maarch courrier instance
 */
// This one is a docker instance, IP may vary, use docker inspect to check.
define('COURRIER_URL', 'http://demo.maarchcourrier.com');
// The online demo for maarch courrier :
//define('COURRIER_URL', 'http://demo.maarchcourrier.com')

/**
 * Configuration for: Database
 * This is the place where you define your database credentials, database type etc.
 */
define('DB_TYPE', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'MaarchCourrier');
define('DB_USER', 'maarch');
define('DB_PASS', 'maarch');
define('DB_CHARSET', 'utf8mb4');

