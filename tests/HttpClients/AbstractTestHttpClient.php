<?php

abstract class AbstractTestHttpClient extends PHPUnit_Framework_TestCase
{

  protected $fakeRawRedirectHeader = "HTTP/1.1 302 Found
Content-Type: text/html; charset=utf-8
Location: https://foobar.com/\r\n\r\n";
  protected $fakeRawProxyHeader = "HTTP/1.0 200 Connection established\r\n\r\n";
  protected $fakeRawProxyHeader2 = "HTTP/1.0 200 Connection established
Proxy-agent: Kerio Control/7.1.1 build 1971\r\n\r\n";
  protected $fakeRawHeader = "HTTP/1.1 200 OK
Etag: \"9d86b21aa74d74e574bbb35ba13524a52deb96e3\"
Content-Type: text/javascript; charset=UTF-8
X-FB-Rev: 9244768
Pragma: no-cache
Expires: Sat, 01 Jan 2000 00:00:00 GMT
Connection: close
Date: Mon, 19 May 2014 18:37:17 GMT
X-FB-Debug: 02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=
Content-Length: 29
Cache-Control: private, no-cache, no-store, must-revalidate
Access-Control-Allow-Origin: *\r\n\r\n";
  protected $fakeRawBody = "{\"id\":\"123\",\"name\":\"Foo Bar\"}";
  protected $fakeHeadersAsArray = array(
    'http_code' => 'HTTP/1.1 200 OK',
    'Etag' => '"9d86b21aa74d74e574bbb35ba13524a52deb96e3"',
    'Content-Type' => 'text/javascript; charset=UTF-8',
    'X-FB-Rev' => '9244768',
    'Pragma' => 'no-cache',
    'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
    'Connection' => 'close',
    'Date' => 'Mon, 19 May 2014 18:37:17 GMT',
    'X-FB-Debug' => '02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=',
    'Content-Length' => '29',
    'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
    'Access-Control-Allow-Origin' => '*',
  );

}
