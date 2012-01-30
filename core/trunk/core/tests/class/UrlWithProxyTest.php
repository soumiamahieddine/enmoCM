<?php


require_once dirname(__FILE__) . '/../../class/Url.php';

class UrlWithProxyTest extends PHPUnit_Framework_TestCase
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
        $_SERVER['HTTP_HOST'] = $url_parts[2];
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

    protected function _patchServerProxyVars($url)
    {
        $url_parts = explode("/", $url, 4);
        $_SERVER['HTTP_X_FORWARDED_PROTO'] =
        (strpos($url_parts[0], 's') !== false) ? 'https' : 'http';

        $host_parts = explode(':', $url_parts[2]);

        $_SERVER['HTTP_X_FORWARDED_HOST'] = $host_parts[0];

        if (count($host_parts) == 2) {
            $_SERVER['HTTP_X_FORWARDED_PORT'] = $host_parts[1];
        } else {
            $_SERVER['HTTP_X_FORWARDED_PORT'] =
            ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? '443' : '80';
        }
        $_SERVER['HTTP_X_FORWARDED_SCRIPT_NAME'] =
        	'/' . ((count($url_parts) > 3) ? $url_parts[3] : '');

        if ((count($url_parts) > 3)) {
            $uriParts = explode("?", $url_parts[3], 2);
            $_SERVER['HTTP_X_FORWARDED_SCRIPT_NAME'] = '/' . $uriParts[0];
        } else {
            $_SERVER['HTTP_X_FORWARDED_SCRIPT_NAME'] = "";
        }
    }


    public function test_different_hosts_root_url()
    {
        $testCase = array('http://example.com/entreprise/index.php',
                      'http://internal.local/entreprise/index.php',
                      'http://example.com/entreprise/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
    }

    public function test_different_hosts_apps_url()
    {
        $testCase = array('http://example.com/entreprise/apps/maarch_entreprise/index.php',
                  'http://internal.local/entreprise/apps/maarch_entreprise/index.php',
                  'http://example.com/entreprise/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
    }

    public function test_different_ports_frontend_non_standard()
    {
        $testCase = array('http://example.com:8080/entreprise/apps/maarch_entreprise/index.php',
                  'http://internal.local/entreprise/apps/maarch_entreprise/index.php',
                  'http://example.com:8080/entreprise/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
    }

    public function test_test_different_ports_backend_non_standard()
    {
        $testCase = array('http://example.com/entreprise/apps/maarch_entreprise/index.php',
                  'http://internal.local:8080/entreprise/apps/maarch_entreprise/index.php',
                  'http://example.com/entreprise/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
    }

    public function test_test_different_ports_both_non_standard()
    {
        $testCase = array('http://example.com:8080/entreprise/apps/maarch_entreprise/index.php',
                      'http://internal.local:8081/entreprise/apps/maarch_entreprise/index.php',
                      'http://example.com:8080/entreprise/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
    }

    public function test_different_hosts_and_uris()
    {
        $testCase = array('http://entreprise.example.com/apps/maarch_entreprise/index.php',
                      'http://internal.local/entreprise/apps/maarch_entreprise/index.php',
                      'http://entreprise.example.com/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
        $this->assertEquals('/apps/maarch_entreprise/index.php',
                            Url::requestUri());
    }

    public function test_different_hosts_and_uris_root_index()
    {
        $testCase = array('http://entreprise.example.com/index.php',
                          'http://internal.local/entreprise/index.php',
                          'http://entreprise.example.com/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
        $this->assertEquals('/index.php',
                            Url::requestUri());
    }



    public function test_different_protocols()
    {
        $testCase = array('https://example.com/entreprise/apps/maarch_entreprise/index.php',
                      'http://internal.local/entreprise/apps/maarch_entreprise/index.php',
                      'https://example.com/entreprise/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
    }

    public function test_different_protocols_on_non_standard_ports()
    {
        $testCase = array('https://example.com:8043/entreprise/apps/maarch_entreprise/index.php',
                      'http://internal.local:8080/entreprise/apps/maarch_entreprise/index.php',
                      'https://example.com:8043/entreprise/');
        call_user_func_array(array($this, "_test_implementation"),
                             $testCase);
    }


    public function test_with_query_string()
    {
        $testCase = array('https://example.com/index.php?foo=bar',
                              'http://internal.local/index.php?foo=bar',
                              'https://example.com/');
        call_user_func_array(array($this, "_test_implementation"),
        $testCase);
        $this->assertEquals('/index.php', Url::scriptName());
        $this->assertEquals('/index.php?foo=bar', Url::requestUri());
    }




    public function _test_implementation($proxyUrl, $url, $expectedCoreUrl) {
        $this->_patchServerVars($url);
        $this->_patchServerProxyVars($proxyUrl);
        $this->assertEquals($expectedCoreUrl, Url::coreurl());
    }
}