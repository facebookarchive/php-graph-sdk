<?php
/**
 *
 * Facebook PHP SDK v4 redirect login / logout / get profile demo
 *
 * Extension of the provided quick start code snippets to allow a better understanding of the SDK's basic functionality.
 * 
 * This example consists of 3 scripts. This one uses FacebookRedirectLoginHelper to authenticate and redirect
 * to the callback script, redirect-login-callback.php, which stores the client token.
 *
 * The third script, get-profile.php, gets a FacebookSession from the stored token, makes a request for the 
 * user profile and processes the response, displaying it on the screen.  
 *
 * Set up:
 *
 *  - Download and extract the latest release of SDK (https://github.com/facebook/facebook-php-sdk-v4/releases)
 *    or clone the repo on a public folder of your web server.
 *
 *  - This script should be on the 'examples' subfolder of the project
 *
 *  - Fill in the app id and secret on tests/FacebookTestCredentials.php.dist and save it as 
 *    tests/FacebookTestCredentials.php 
 * 
 *  - Fill in login, logout urls below.
 *
 * Don't forget to allow myappdomain.com on your facebook app settings page (You'll find a link to it on
 * http://developers.facebook.com/apps where you can also create a new app and find the app's id and secret).
 *
 * Usage:
 *
 *  - Point your browser to http://whatever.myappdomain.com/path/to/redirect-login.php
 *
 * PHP >= 5.4 IS REQUIRED TO RUN VERSION 4.X.X OF Facebook PHP SDK
 *
 */

use Facebook\FacebookRedirectLoginHelper;
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

// login url (in this example, the URL pointing to redirect-login-callback script)
$loginRedirectUrl = 'http://whatever.myappdomain.com/path/to/redirect-login-callback.php';
// logout url (in this example, the URL pointing to this script, with logout param set)
$logoutRedirectUrl = 'http://whatever.myappdomain.com/path/to/redirect-login.php?logout=true';

// comma separated list of requested permissions (on top of public profile)
$scope = 'email';

if (isset($_GET['logout'])){

    // New session
    $_SESSION = array();
    session_regenerate_id(true);
    $_SESSION['fbLoginRedirectUrl'] = $loginRedirectUrl;
    $_SESSION['fbLogoutRedirectUrl'] = $logoutRedirectUrl;

}

if (isset($_SESSION['fb_token'])){

    header ('Location: get-profile.php');
    die();

}

FacebookSession::setDefaultApplication(FacebookTestCredentials::$appId, FacebookTestCredentials::$appSecret);

$helper = new FacebookRedirectLoginHelper($loginRedirectUrl);

try {

    // New session
    $_SESSION = array();
    session_regenerate_id(true);
    $_SESSION['fbLoginRedirectUrl'] = $loginRedirectUrl;
    $_SESSION['fbLogoutRedirectUrl'] = $logoutRedirectUrl;

    $out = "<p><a href="
        . $helper->getLoginUrl(
            array(
                'scope' =>
                    $scope
            )
        )
        . ">
            <img 
                src='http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_large_long.gif' 
                alt='Log in with Facebook'
                title='Log in with Facebook'
            />
         </a></p>";

} catch(Exception $ex) {

    $out = "<p>Exception ".$ex->getMessage()."</p>";

}
?><!doctype html>
<html>
<head>
    <title>
        Facebook redirect login test page
    </title>
</head>
<body>
<?=$out?>
</body>
</html>
