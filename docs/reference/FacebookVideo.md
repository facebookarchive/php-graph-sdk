<card>
# Video Uploading with the Facebook SDK for PHP

Uploading video files to the Graph API is made a breeze with the SDK for PHP.
</card>

<card>
## Facebook\FileUpload\FacebookVideo(string $pathToVideoFile, int $maxLength = -1, int $offset = -1) {#overview}

The `FacebookVideo` entity represents a local or remote video file to be uploaded with a request to Graph.

There are two ways to instantiate a `FacebookVideo` entity. One way is to instantiate it directly:

~~~~
use Facebook\FileUpload\FacebookVideo;

$myVideoFileToUpload = new FacebookVideo('/path/to/video-file.mp4');
~~~~

Alternatively, you can use the `videoToUpload()` factory on the `Facebook\Facebook` super service to instantiate a new `FacebookVideo` entity.

~~~~
$fb = new Facebook\Facebook(/* . . . */);

$myVideoFileToUpload = $fb->videoToUpload('/path/to/video-file.mp4'),
~~~~

Partial file uploads are possible using the `$maxLength` and `$offset` parameters which provide the same functionality as the `$maxlen` and `$offset` parameters on the [`stream_get_contents()` PHP function](http://php.net/stream_get_contents).
</card>

<card>
## Usage {#usage}

In Graph v2.3, functionality was added to [upload video files in chunks](/docs/graph-api/video-uploads#resumable). The PHP SDK provides a handy API to easily upload video files in chunks via the [`uploadVideo()` method](/docs/php/Facebook#upload-video).

~~~~
// Upload a video for a user (chunked)
$data = [
  'title' => 'My awesome video',
  'description' => 'More info about my awesome video.',
];

try {
  $response = $fb->uploadVideo('me', '/path/to/video.mp4', $data, '{user-access-token}');
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Error: ' . $e->getMessage();
  exit;
}

echo 'Video ID: ' . $response['video_id'];
~~~~

For versions of Graph before v2.3, videos had to be uploaded in one request.

~~~~
// Upload a video for a user
$data = [
  'title' => 'My awesome video',
  'description' => 'More info about my awesome video.',
  'source' => $fb->videoToUpload('/path/to/video.mp4'),
];

try {
  $response = $fb->post('/me/videos', $data);
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'Video ID: ' . $graphNode['id'];
~~~~
</card>
