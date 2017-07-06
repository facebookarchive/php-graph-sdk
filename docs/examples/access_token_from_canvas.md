# Get Access Token From App Canvas Example

This example covers obtaining an access token and signed request from within the context of an app canvas with the Facebook SDK for PHP.

## Example

A signed request will be sent to your app via the HTTP POST method within the context of app canvas. The PHP SDK provides a helper to validate & decode the signed request.

```php
$fb = new Facebook\Facebook([
    'app_id' => '{app-id}',
    'app_secret' => '{app-secret}',
    'default_graph_version' => 'v2.9',
]);

try {
    $signedRequest = new SignedRequest($fb->getApp(), $_POST['signed_request'])
    $accessToken = $signedRequest->getAccessToken();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (!isset($accessToken)) {
    echo 'No OAuth data could be obtained from the signed request. User has not authorized your app yet.';
    exit;
}

// Logged in
echo '<h3>Signed Request</h3>';
var_dump($signedRequest->getPayload());

echo '<h3>Access Token</h3>';
var_dump($accessToken->getValue());
```
