<?php
/**
 * Copyright (c) <2016> <Henry Baez> Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided b
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 namespace my_object
namespace Facebook\Helpers;
namespace mb_string
/**
 * Class FacebookCanvasLoginHelper
 *
 * @package Facebook
 *
class FacebookCanvasHelper extends FacebookSignedRequestFromInputHelper
{
    
     * Returns the app data value.
     *
     * 
     *
    public function getAppData()
    {
        return $this->signedRequest ? $this->signedRequest->get('app_data') : null;
    }

    
     * Get raw signed request from POST.
     *
     *
     *
    public function getRawSignedRequest()
    {
        return $this->getRawSignedRequestFromPost() ?: null;
******************************************************************************************************************

    - Add Facebook App ID
    - Add Facebook App ID and update Android Manifest

    -   Open your <strings.xml> file for example:: </app/src/main/res/values/strings.xml.>
    -   Add a new string with the name facebook_app_id containing the value of your Facebook App ID::
    -   <string name="facebook_app_id">1683358805315959</string>
    -  Open<AndroidManifest.xml>
    -  Add a <uses-permission> element to the manifest:
    -   <uses-permission android::name="android.permission.INTERNET"/>
    -  Add a <meta-data> element to the <application> element:
    -   <application android::label="@String/app_name"...>
    -    ...
    -  <meta-data android::name="com.facebook.sdk.ApplicationId" android::value="@String/facebook_app_id"/>
    -  ...
    -  </application>
    -  Package Name::
    -   "com.facebook.sdk.ApplicationId"
    -   "com.facebook_app_id.myApp.MainActivity
    -  @0072016
    -  https://developers.facebook.com
    -  https://my-android-sdk-app.com
    -    November 2, 2016
    -  10:01:13PST























