<?php
/**
 *
 * Facebook PHP SDK v4 redirect login / logout / get profile demo script
 *
 * Extension of the provided quick start code snippets to allow a better understanding of the SDK's basic functionality.
 *
 * Set up:
 *
 *  - Download and extract the latest release of SDK (https://github.com/facebook/facebook-php-sdk-v4/releases)
 *    or clone the repo on a public folder of your web server.
 *
 *  - This script should be on the 'examples' subfolder of the project
 *
 *  - Before running this script you must install the vendor dependencies using composer (http://getcomposer.org)
 *
 *  - Finally: fill the app id, secret and login, logout urls below.
 *
 * Don't forget to allow myappdomain.com on your facebook app settings page (You'll find a link to it on
 * http://developers.facebook.com/apps where you can also create a new app and find the app's id and secret).
 *
 * Usage:
 *
 *  - Point your browser to http://whatever.myappdomain.com/path/to/redirect-login-and-get-profile-example.php
 *
 * PHP >= 5.4 IS REQUIRED TO RUN VERSION 4.X.X OF Facebook PHP SDK
 *
 */

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;

session_start();

require_once '../vendor/autoload.php';

// login url (in this example, the URL pointing to this script)
$loginRedirectUrl = 'http://whatever.myappdomain.com/path/to/redirect-login-and-get-profile-example.php';
// logout url (in this example, the URL pointing to this script, with logout param set)
$logoutRedirectUrl = 'http://whatever.myappdomain.com/path/to/redirect-login-and-get-profile-example.php?logout=true';
// app id
$appId = '';
// app secret
$appSecret = '';

// comma separated list of requested permissions (on top of public profile)
$scope = 'email';

FacebookSession::setDefaultApplication($appId, $appSecret);
$out = '';

if (isset($_GET['logout'])){

    // New session
    $_SESSION = array();
    session_regenerate_id(true);

}

$helper = new FacebookRedirectLoginHelper($loginRedirectUrl);

try {

    if (isset($_SESSION['fb_token'])){

        $session = new FacebookSession($_SESSION['fb_token']);

    } else {

        $session = $helper->getSessionFromRedirect();

    }

    if (isset($session)) {

        $_SESSION['fb_token'] = $session->getToken();

        // Logged in
        $request = new FacebookRequest($session, 'GET', '/me');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();

        $out = "<p><a href=".$helper->getLogoutUrl($session, $logoutRedirectUrl).">logout</a></p>";
        $out .= "<p>ME:</p><p><pre><code>" . var_export($graphObject, true)."</code></pre></p>";

    } else {

        // New session
        $_SESSION = array();
        session_regenerate_id(true);

        $out = "<p><a href="
            . $helper->getLoginUrl(
                array(
                    'scope' =>
                        $scope
                )
            )
            . ">login</a></p>";

    }

} catch(FacebookRequestException $ex) {

    // When Facebook returns an error

    $out = "<p>FacebookRequestException: ".$ex->getMessage()."</p>";

    // New session
    $_SESSION = array();
    session_regenerate_id(true);

    $out .= "<p><a href="
        . $helper->getLoginUrl(
            array(
                'scope' =>
                    $scope
            )
        )
        . ">login</a></p>";

} catch(Exception $ex) {

    // When validation fails or other local issues

    $out = "<p>Exception ".$ex->getMessage()."</p>";

    // New session
    $_SESSION = array();
    session_regenerate_id(true);

    $out .= "<p><a href="
        . $helper->getLoginUrl(
            array(
                'scope' =>
                    $scope
            )
        )
        . ">login</a></p>";

}
?><!doctype html>
<html>
<head>
    <title>
        facebook redirect login / logout / get profile test page
    </title>
</head>
<body>
<?=$out?>
</body>
</html>
