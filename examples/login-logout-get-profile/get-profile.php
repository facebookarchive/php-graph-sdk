<?php
/**
 * Facebook PHP SDK v4 redirect login / logout / get profile demo
 * 
 * Gets the profile of a logged in user, using the session obtained from the token stored 
 * on $_SESSION['fb_token']
 * 
 */

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;

session_start();

// If vendor dependencies are installed (see http://getcomposer.org), requirements can be autoloaded:
// require_once '../../vendor/autoload.php';
require_once '../../tests/FacebookTestCredentials.php';
require_once '../../src/Facebook/FacebookSDKException.php';
require_once '../../src/Facebook/FacebookRequestException.php';
require_once '../../src/Facebook/FacebookAuthorizationException.php';
require_once '../../src/Facebook/FacebookClientException.php';
require_once '../../src/Facebook/FacebookServerException.php';
require_once '../../src/Facebook/FacebookPermissionException.php';
require_once '../../src/Facebook/FacebookThrottleException.php';
require_once '../../src/Facebook/FacebookOtherException.php';
require_once '../../src/Facebook/GraphObject.php';
require_once '../../src/Facebook/GraphSessionInfo.php';
require_once '../../src/Facebook/FacebookSession.php';
require_once '../../src/Facebook/FacebookRedirectLoginHelper.php';
require_once '../../src/Facebook/FacebookRequest.php';
require_once '../../src/Facebook/FacebookResponse.php';

FacebookSession::setDefaultApplication(FacebookTestCredentials::$appId, FacebookTestCredentials::$appSecret);

if (isset($_SESSION['fbLoginRedirectUrl'])){

    $helper = new FacebookRedirectLoginHelper($_SESSION['fbLoginRedirectUrl']);

} else {

    header ('Location: redirect-login.php?logout=true');
    die();

}

try {

    if (isset($_SESSION['fb_token'])){

        $session = new FacebookSession($_SESSION['fb_token']);

    } else {

        header ('Location: redirect-login.php?logout=true');
        die();

    }

    if (isset($session)) {
        
        // Logged in
        $request = new FacebookRequest($session, 'GET', '/me');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();

        $out = "<p><a href=".$helper->getLogoutUrl($session, $_SESSION['fbLogoutRedirectUrl']).">logout</a></p>";
        $out .= "<p>ME:</p><p><pre><code>" . var_export($graphObject, true)."</code></pre></p>";

    } else {

        header ('Location: redirect-login.php?logout=true');
        die();

    }

} catch(FacebookRequestException $ex) {

    // When Facebook returns an error

    $out = "<p>FacebookRequestException: ".$ex->getMessage()."</p>";
    $out .= "<p><a href=\"redirect-login.php?logout=true\">go to login page</a></p>";

} catch(Exception $ex) {

    // When validation fails or other local issues

    $out = "<p>Exception ".$ex->getMessage()."</p>";
    $out .= "<p><a href=\"redirect-login.php?logout=true\">go to login page</a></p>";

}
?><!doctype html>
<html>
<head>
    <title>
        Facebook get profile test page
    </title>
</head>
<body>
<?=$out?>
</body>
</html>