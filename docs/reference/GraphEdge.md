# GraphEdge for the Facebook SDK for PHP

When a list of nodes is returned from a Graph request, it can be cast as a `GraphEdge` which provides convenient ways of interacting with the data which includes pagination.

## Facebook\GraphNodes\GraphEdge

You can grab a `GraphEdge` from a response from Graph.

```php
$graphEdge = $request->getGraphEdge();
```

Usage:

```php
// Iterate over all the GraphNode's returned from the edge
foreach ($graphEdge as $graphNode) {
  // . . .
}
```

## Pagination

With the help of the `Facebook\Facebook` super service class, the `GraphEdge` collection can grab the next and previous sets of data.

```php
$albumsEdge = $response->getGraphEdge();

// Get the next page of results
$nextPageOfAlbums = $fb->next($albumsEdge);
// Or the previous page of results
$previousPageOfAlbums = $fb->previous($previousOfAlbums);
```

When the next or previous page returns no results, `$fb->next()` will return `null`.

## Deep Pagination

Sometimes Graph will return a list of nodes within a node. Paginating on these sub lists can be non-trivial. Fortunately, the `GraphEdge` collection takes the guesswork out and allows you to paginate deeply within a `GraphEdge`.

The following example paginates over the first 5 pages of a list of Facebook pages. For each page it paginates over all the likes for that page.

```php
$pagesEdge = $response->getGraphEdge();
// Only grab 5 pages
$maxPages = 5;
$pageCount = 0;

do {
  echo '<h1>Page #' . $pageCount . ':</h1>' . "\n\n";

  foreach ($pagesEdge as $page) {
    var_dump($page->asArray());

    $likes = $page['likes'];
    do {
      echo '<p>Likes:</p>' . "\n\n";
      var_dump($likes->asArray());
    } while ($likes = $fb->next($likes));
  }
  $pageCount++;
} while ($pageCount < $maxPages && $pagesEdge = $fb->next($pagesEdge));
```

## Method Reference

### getMetaData()
```php
public array getMetaData()
```

Sometimes Graph will return additional data associated with an edge. You can access this raw data as an array with `getMetaData()`.

```php
$metaData = $graphEdge->getMetaData();
```

### getNextCursor()
```php
public string|null getNextCursor()
```

Returns the `$.paging.cursors.after` value if it exists or `null` if it does not exist. Since cursors are sort of like bookmarks for paginating over an edge, it is sometimes handy to store the last cursor used so that you can revisit the exact position at a later time.

```php
$nextCursor = $graphEdge->getNextCursor();
// Returns: MMAyDDM5NjA0OTEyMDc0OTM=
```

### getPreviousCursor()
```php
public string|null getPreviousCursor()
```

Returns the `$.paging.cursors.before` value if it exists or `null` if it does not exist.

```php
$previousCursor = $graphEdge->getPreviousCursor();
// Returns: ODOxMTUzMjQzNTg5zzU5
```

### getTotalCount()
```php
public int|null getTotalCount()
```

Some endpoints and edges of Graph support a summary of data. If the `summary=true` modifier was sent with a request on a supported endpoint or edge, Graph will return the total count of results in the meta data under `$.summary.total_count`. `getTotalCount()` will return that value or `null` if it does not exist.

```php
$response = $fb->get('/{post-id}/likes?summary=true');
$likesEdge = $response->getGraphEdge();
$totalCount = $likesEdge->getTotalCount();
// Returns: 10
```
