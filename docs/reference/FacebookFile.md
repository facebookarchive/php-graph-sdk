# File Uploading with the Facebook SDK for PHP

Uploading files to the Graph API is made a breeze with the Facebook SDK for PHP.

## Facebook\FileUpload\FacebookFile(string $pathToFile, int $maxLength = -1, int $offset = -1)

The `FacebookFile` entity represents a local or remote file to be uploaded with a request to Graph.

There are two ways to instantiate a `FacebookFile` entity. One way is to instantiate it directly:

```php
use Facebook\FileUpload\FacebookFile;

$myFileToUpload = new FacebookFile('/path/to/file.jpg');
```

Alternatively, you can use the `fileToUpload()` factory on the `Facebook\Facebook` super service to instantiate a new `FacebookFile` entity.

```php
$fb = new Facebook\Facebook(/* . . . */);

$myFileToUpload = $fb->fileToUpload('/path/to/file.jpg');
```

Partial file uploads are possible using the `$maxLength` and `$offset` parameters which provide the same functionality as the `$maxlen` and `$offset` parameters on the [`stream_get_contents()` PHP function](http://php.net/stream_get_contents).

## Usage

The following example uploads a photo for a user.

```php
$data = [
  'message' => 'My awesome photo upload example.',
  'source' => $fb->fileToUpload('/path/to/photo.jpg'),
  // Or you can provide a remote file location
  //'source' => $fb->fileToUpload('https://example.com/photo.jpg'),
];

try {
  $response = $fb->post('/me/photos', $data);
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Photo ID: ' . $graphNode['id'];
```

> **Note:** Although you can use `fileToUpload()` to upload a remote file, it is more efficient to just point the Graph request to the the remote file with the `url` param.

```php
// Upload a remote photo for a user without using the FacebookFile entity
$data = [
  'message' => 'A neat photo upload example. Neat.',
  'url' => 'https://example.com/photo.jpg',
];

$response = $fb->post('/me/photos', $data);
```
