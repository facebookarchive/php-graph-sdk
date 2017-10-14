# Retrieve User Profile via the Graph API

This example covers getting profile information for the current user and printing their name, using the Graph API and the Facebook SDK for PHP.

It assumes that you've already obtained an access token from one of the helpers found [here](../reference.md).

For more information, see the documentation for [`Facebook\Facebook`](../reference/Facebook.md), [`Facebook\FacebookResponse`](../reference/FacebookResponse.md), [`Facebook\GraphNodes\GraphUser`](../reference/GraphNode.md#graphuser-instance-methods), [`Facebook\Exceptions\FacebookSDKException`](../reference/FacebookSDKException.md) and [`Facebook\Exceptions\FacebookResponseException`](../reference/FacebookResponseException.md).

## Example

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  ]);

try {
  // Returns a `Facebook\FacebookResponse` object
  $response = $fb->get('/me?fields=id,name', '{access-token}');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$user = $response->getGraphUser();

echo 'Name: ' . $user['name'];
// OR
// echo 'Name: ' . $user->getName();
```
