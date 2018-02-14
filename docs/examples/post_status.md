# Post Status Update Example

This example covers posting status update on user's timeline with the Facebook SDK using PHP.

It assumes that you've already obtained an access token from one of the helpers found [here](../reference.md). The access token must have the `publish_actions` permission for this to work.

## Example

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.12',
  ]);

$data = [
  'message' => 'post update - 43'
];

try {
  $response = $fb->post('/me/feed', $data, '{user-access-token}');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Posted with id: ' . $graphNode['id'];
```

Note that the 'message' field must come from the user, as pre-filled content is forbidden by the [Platform Policies](https://developers.intern.facebook.com/policy/#control) (2.3).
