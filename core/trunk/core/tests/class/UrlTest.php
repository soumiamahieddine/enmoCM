<?php

require_once dirname(__FILE__) . '/../../class/Url.php';

class UrlTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        global $_SERVER;
        $this->_old_values = $_SERVER;
        $url = 'http://example.com/entreprise/apps/maarch_entreprise/index.php';
        $this->_patchServerVars($url);
    }

    public function tearDown()
    {
        $_SERVER = $this->_old_values;
        Url::forget();
    }

    protected function _patchServerVars($url)    {
        global $_SERVER;
        $url_parts = explode("/", $url, 4);
        $_SERVER['HTTPS'] = (strpos($url_parts[0], 's') !== false)
                                ? 'on': '';
        $host_parts = explode(':', $url_parts[2]);
        $_SERVER['HTTP_HOST'] = $host_parts[0];
        if (count($host_parts) == 2) {
            $_SERVER['SERVER_PORT'] = $host_parts[1];
        } else {
            $_SERVER['SERVER_PORT'] = ($_SERVER['HTTPS'] === 'on')
                                        ? '443' : '80';
        }
        if ((count($url_parts) > 3)) {
            $uriParts = explode("?", $url_parts[3], 2);
            $_SERVER['SCRIPT_NAME'] = '/' . $uriParts[0];
            $_SERVER['QUERY_STRING'] = (count($uriParts) > 1)
                                         ? $uriParts[1] : '';
        } else {
            $_SERVER['SCRIPT_NAME'] = "";
            $_SERVER['QUERY_STRING'] = "";
        }
    }



    public function test_cache_cleared_on_forget() {
        $old = Url::coreurl();
        Url::forget();


        $url = 'http://foo.com'
             . '/bar';
        $this->_patchServerVars($url);

        $new = Url::coreurl();

        $this->assertNotEquals($old, $new);
    }

    public function test_cache_must_persist_between_two_instances()
    {
        $old = Url::coreurl();
        unset($u);

        $this->assertEquals('example.com', Url::host());
    }

    public function test_http_subdir_url_from_apps_index()
    {
        $url = 'http://example.com'
             . '/entreprise/apps/maarch_entreprise/index.php';
        $this->_patchServerVars($url);

        $this->assertEquals(
            'http://example.com/entreprise/',
            Url::coreurl()
        );
    }

    public function test_http_subdir_url_from_root_index()
    {
        $url = 'http://example.com'
             . '/entreprise/index.php';
        $this->_patchServerVars($url);

        $this->assertEquals(
            'http://example.com/entreprise/',
            Url::coreurl()
        );
    }

    public function test_https_subdir()
    {
        $url = 'https://example.com'
             . '/entreprise/index.php';
        $this->_patchServerVars($url);

        $this->assertEquals(
            'https://example.com/entreprise/',
            Url::coreurl()
        );
        $this->assertEquals('https', Url::proto());
        $this->assertEquals('443', Url::port());
    }

    public function test_http_url_at_server_root_root_index()
    {
        $url = 'http://example.com'
             . '/index.php';
        $this->_patchServerVars($url);

        $this->assertEquals(
            'http://example.com/',
            Url::coreurl()
        );
        $this->assertEquals('/', Url::baseUri());
        $this->assertEquals('/index.php', Url::requestUri());
    }

    public function test_http_url_at_server_root_apps_index()
    {
        $url = 'http://example.com'
             . '/apps/maarch_entreprise/index.php';
        $this->_patchServerVars($url);

        $this->assertEquals(
            'http://example.com/',
            Url::coreurl()
        );
        $this->assertEquals('/', Url::baseUri());
        $this->assertEquals('/apps/maarch_entreprise/index.php',
                            Url::requestUri());
    }

    public function test_http_non_standard_port()
    {
        $url = 'http://example.com:8080'
             . '/entreprise/index.php';
        $this->_patchServerVars($url);

        $this->assertEquals(
            'http://example.com:8080/entreprise/',
            Url::coreurl()
        );
        $this->assertEquals('http', Url::proto());
        $this->assertEquals('8080', Url::port());
    }

    public function test_https_non_standard_port()
    {
        $url = 'https://example.com:8043'
             . '/entreprise/index.php';
        $this->_patchServerVars($url);

        $this->assertEquals(
            'https://example.com:8043/entreprise/',
            Url::coreurl()
        );
        $this->assertEquals('https', Url::proto());
        $this->assertEquals('8043', Url::port());
    }

    public function test_query_string()
    {
        $url = 'https://example.com'
             . '/entreprise/index.php?foo=bar';
        $this->_patchServerVars($url);

        $this->assertEquals(
                'https://example.com/entreprise/',
                Url::coreurl()
        );
        $this->assertEquals('/entreprise/index.php', Url::scriptName());
        $this->assertEquals('/entreprise/index.php?foo=bar', Url::requestUri());
    }
}

#HTTPS

#'SERVER_PORT' => 80
#'HTTP_HOST' => 'example.com'
#'SCRIPT_NAME' => 'entreprise/apps/maarch_entreprise/index.php'

#HTTP_X_FORWARDED_HOST
#HTTP_X_FORWARDED_PORT
#HTTP_X_FORWARDED_PROTO

#HTTP_BASE_URL / HTTP_X_FORWARDED_PROTO / PATH_INFO
