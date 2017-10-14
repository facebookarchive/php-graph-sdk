# Pagination Example

This example covers basic cursor pagination with the Facebook SDK for PHP.

## Example

The Graph API supports [several methods to paginate over response data](https://developers.facebook.com/docs/graph-api/using-graph-api/#paging). The PHP SDK supports cursor-based pagination out of the box. It does all the heavy lifting of managing page cursors for you.

In this example we'll pull five entries from a user's feed (assuming the user approved the `read_stream` permission for your app). Then we'll use the `next()` method to grab the next page of results. Naturally you'd provide some sort of pagination navigation in your app, but this is just an example to get you started.

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  ]);

try {
  // Requires the "read_stream" permission
  $response = $fb->get('/me/feed?fields=id,message&limit=5');
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

// Page 1
$feedEdge = $response->getGraphEdge();

foreach ($feedEdge as $status) {
  var_dump($status->asArray());
}

// Page 2 (next 5 results)
$nextFeed = $fb->next($feedEdge);

foreach ($nextFeed as $status) {
  var_dump($status->asArray());
}
```
