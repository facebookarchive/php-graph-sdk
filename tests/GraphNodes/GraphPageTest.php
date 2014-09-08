<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
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
 */
namespace Facebook\Tests\GraphNodes;

use Mockery as m;
use Facebook\GraphNodes\GraphObjectFactory;

class GraphPageTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var \Facebook\Entities\FacebookResponse
   */
  protected $responseMock;

  public function setUp()
  {
    $this->responseMock = m::mock('\\Facebook\\Entities\\FacebookResponse');
  }

  public function testPagePropertiesReturnGraphPageObjects()
  {
    $dataFromGraph = [
      'id' => '123',
      'name' => 'Foo Page',
      'best_page' => [
        'id' => '1',
        'name' => 'Bar Page',
      ],
      'global_brand_parent_page' => [
        'id' => '2',
        'name' => 'Faz Page',
      ],
    ];

    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn($dataFromGraph);
    $factory = new GraphObjectFactory($this->responseMock);
    $graphObject = $factory->makeGraphPage();

    $bestPage = $graphObject->getBestPage();
    $globalBrandParentPage = $graphObject->getGlobalBrandParentPage();

    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphPage', $bestPage);
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphPage', $globalBrandParentPage);
  }

  public function testLocationPropertyWillGetCastAsGraphLocationObject()
  {
    $dataFromGraph = [
      'id' => '123',
      'name' => 'Foo Page',
      'location' => [
        'city' => 'Washington',
        'country' => 'United States',
        'latitude' => 38.881634205431,
        'longitude' => -77.029121075722,
        'state' => 'DC',
      ],
    ];

    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn($dataFromGraph);
    $factory = new GraphObjectFactory($this->responseMock);
    $graphObject = $factory->makeGraphPage();

    $location = $graphObject->getLocation();

    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphLocation', $location);
  }

}
