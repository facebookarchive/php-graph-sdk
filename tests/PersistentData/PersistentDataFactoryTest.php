<?php

declare(strict_types=1);
/**
 * Copyright 2017 Facebook, Inc.
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
namespace Facebook\Tests\PersistentData;

use Facebook\PersistentData\FacebookMemoryPersistentDataHandler;
use Facebook\PersistentData\FacebookSessionPersistentDataHandler;
use Facebook\PersistentData\PersistentDataFactory;
use Facebook\PersistentData\PersistentDataInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class PersistentDataFactoryTest
 */
class PersistentDataFactoryTest extends TestCase
{
    const COMMON_NAMESPACE = 'Facebook\PersistentData\\';
    const COMMON_INTERFACE = PersistentDataInterface::class;

    /**
     * @param mixed  $handler
     * @param string $expected
     *
     * @dataProvider persistentDataHandlerProviders
     */
    public function testCreatePersistentDataHandler(mixed $handler, string $expected): void
    {
        $persistentDataHandler = PersistentDataFactory::createPersistentDataHandler($handler);

        static::assertInstanceOf(self::COMMON_INTERFACE, $persistentDataHandler);
        static::assertInstanceOf($expected, $persistentDataHandler);
    }

    /**
     * @return array
     */
    public function persistentDataHandlerProviders(): array
    {
        $handlers = [
            ['memory', self::COMMON_NAMESPACE . 'FacebookMemoryPersistentDataHandler'],
            [new FacebookMemoryPersistentDataHandler(), self::COMMON_NAMESPACE . 'FacebookMemoryPersistentDataHandler'],
            [new FacebookSessionPersistentDataHandler(false), self::COMMON_NAMESPACE . 'FacebookSessionPersistentDataHandler'],
            [null, self::COMMON_INTERFACE],
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $handlers[] = ['session', self::COMMON_NAMESPACE . 'FacebookSessionPersistentDataHandler'];
        }

        return $handlers;
    }
}
