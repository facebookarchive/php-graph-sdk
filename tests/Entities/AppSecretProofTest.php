<?php

use Facebook\Facebook;
use Facebook\Entities\AppSecretProof;

class AppSecretProofTest extends PHPUnit_Framework_TestCase
{

  public function testAnAppSecretProofIsGeneratedAsExpected()
  {
    $appSecretProof = AppSecretProof::make('foo_access_token', 'foo_app_secret');

    $this->assertEquals('12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95', $appSecretProof);
  }

  public function testAnAppSecretProofWillUseDefaultAppSecret()
  {
    Facebook::setDefaultApplication('123', 'foo_app_secret');

    $appSecretProof = AppSecretProof::make('foo_access_token');

    $this->assertEquals('12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95', $appSecretProof);
  }

  public function testAnAppSecretProofEntityCanBeReturnedAsAString()
  {
    $appSecretProof = new AppSecretProof('foo_access_token', 'foo_app_secret');

    $this->assertEquals('12f5dcbb7557d24b1d37fd180c45991c5999f325ece0af331c00a85d762f2b95', (string) $appSecretProof);
  }

}
