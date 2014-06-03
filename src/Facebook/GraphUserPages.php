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
namespace Facebook;

/**
 * Class GraphUserPages
 * @package Facebook
 * @author Artur Luiz <artur@arturluiz.com.br>
 */
class GraphUserPages extends GraphPage implements \Iterator
{

  /**
   * @var int The current position of GraphUserPage list
   */
  private $position;

  /**
   * @var GraphUserPage|null The current GraphUserPage
   */
  private $currentPage;

  /**
   * Creates a GraphUserPages using the data provided.
   *
   * @param array $raw
   */
  public function __construct($raw)
  {
    parent::__construct($raw);
    $this->position = 0;
  }

  /**
   * Returns the ID for the user as a string if present.
   *
   * @return string|null
   */
  public function getPages()
  {
    return $this->getProperty('data');
  }

  /**
   * Returns the page.
   *
   * @return GraphUserPage|null
   */
  public function current()
  {
    return $this->currentPage;
  }

  /**
   * Returns current key.
   *
   * @return int
   */
  public function key()
  {
    return $this->position;
  }
  
  /**
   * Goes to next page.
   *
   * @return void
   */
  public function next()
  {
    $this->position++;
  }
  
  /**
   * Goes to first page.
   *
   * @return void
   */
  public function rewind()
  {
    $this->position = 0;
  }
  
  /**
   * Returns true if there is any page to show.
   *
   * @return boolean
   */
  public function valid()
  {
    $this->currentPage = $this->getPages()->getProperty($this->position, GraphUserPage::className());
    return !is_null($this->currentPage);
  }

}