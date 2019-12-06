<?php
/**
 * Copyright 2017 Facebook, Inc.
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

use Facebook\Http\GraphRawResponse;
use Facebook\Exceptions\FacebookSDKException;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Ring\Exception\RingException;
use GuzzleHttp\Exception\RequestException;

class FacebookGuzzleHttpClient implements FacebookHttpClientInterface
{
    /**
     * @var \GuzzleHttp\Client The Guzzle client.
     */
    protected $guzzleClient;

    /**
     * @param \GuzzleHttp\Client|null The Guzzle client.
     */
    public function __construct()
    {
        $this->guzzleClient = new Client();
    }

    /**
     * @inheritdoc
     */
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        $request = new Request($method, $url, $headers, $body);

        try {
            $response = $this->guzzleClient->send($request, ['timeout' => $timeOut, 'http_errors' => false]);
        } catch (GuzzleHttp\Exception\RequestException $e) {
            throw new Facebook\Exceptions\FacebookSDKException($e->getMessage(), $e->getCode());
        }

        $httpStatusCode = $response->getStatusCode();
        $responseHeaders = $response->getHeaders();
        foreach ($responseHeaders as $key => $values) {
            $responseHeaders[$key] = implode(', ', $values);
        }

        $responseBody = $response->getBody()->getContents();

        return new GraphRawResponse(
            $responseHeaders,
            $responseBody,
            $httpStatusCode
        );

    }
    
}
