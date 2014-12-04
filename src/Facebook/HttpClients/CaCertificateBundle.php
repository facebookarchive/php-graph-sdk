<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Facebook\HttpClients;

use Facebook\Exceptions\FacebookSDKException;

/**
 * Some things copied from Guzzle
 * @see https://github.com/guzzle/RingPHP/blob/master/src/Client/ClientUtils.php
 */

/**
 * Class CaCertificateBundle
 * @package Facebook
 */
class CaCertificateBundle
{

  /**
   * @var array A list of common locations for a CA certificate bundle.
   */
  protected static $caFiles = [
    // Red Hat, CentOS, Fedora (provided by the ca-certificates package)
    '/etc/pki/tls/certs/ca-bundle.crt',
    // Ubuntu, Debian (provided by the ca-certificates package)
    '/etc/ssl/certs/ca-certificates.crt',
    // FreeBSD (provided by the ca_root_nss package)
    '/usr/local/share/certs/ca-root-nss.crt',
    // OS X provided by homebrew (using the default path)
    '/usr/local/etc/openssl/cert.pem',
    // Windows?
    'C:\\windows\\system32\\curl-ca-bundle.crt',
    'C:\\windows\\curl-ca-bundle.crt',
  ];

  /**
   * Tries to detect the system CA certificate bundle for peer verification.
   *
   * @returns string|null
   *
   * @throws FacebookSDKException
   */
  public static function getCaCertificateBundle()
  {
    if ($ca = ini_get('openssl.cafile')) {
      return $ca;
    }

    if ($ca = ini_get('curl.cainfo')) {
      return $ca;
    }

    foreach (static::$caFiles as $filename) {
      if (file_exists($filename)) {
        return $filename;
      }
    }

    throw new FacebookSDKException(
      'No system CA bundle could be found in any of the the common system locations.
PHP versions earlier than 5.6 are not properly configured to use the system\'s
CA bundle by default. In order to verify peer certificates, you will need to
supply the path on disk to a certificate bundle to the "ca_bundle" option. If you do not
need a specific certificate bundle, then Mozilla provides a commonly used CA
bundle which can be downloaded here (provided by the maintainer of cURL):
https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt. Once
you have a CA bundle available on disk, you can set the \'openssl.cafile\' PHP
ini setting to point to the path to the file, allowing you to omit the verify
request option. See http://curl.haxx.se/docs/sslcerts.html for more
information.'
    );
  }

}
