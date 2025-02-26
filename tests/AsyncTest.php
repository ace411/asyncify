<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

use Chemem\Asyncify\Async;
use PHPUnit\Framework\TestCase;

use function Chemem\Asyncify\call;
use function Chemem\Bingo\Functional\toException;
use function React\Async\await;

use const Chemem\Asyncify\Internal\PHP_THREADABLE;

class AsyncTest extends TestCase
{
  public static function asyncifyProvider(): array
  {
    return [
      [
        [
          (
            PHP_THREADABLE ?
              function (...$args) {
                return \file_get_contents(...$args);
              } :
              '(function (...$args) { return file_get_contents(...$args); })'
          ),
          ['foo.txt']
        ],
        '/(No such file or directory)/i'
      ],
      [
        ['exec', ['echo "foo"']],
        '/^(foo)$/'
      ],
      [
        [
          '(function ($cmd) { return exec($cmd); })',
          ['echo "foo"'],
        ],
        (
          PHP_THREADABLE ?
            '/(Call to undefined function)/i' :
            '/^(foo)$/'
        )
      ],
      [
        [
          (
            PHP_THREADABLE ?
              function (string $value) {
                return ['foo' => $value];
              } :
              '(function (string $value) { return ["foo" => $value]; })'
          ),
          ['foo']
        ],
        ['foo' => 'foo'],
      ],
      [
        [
          (
            PHP_THREADABLE ?
              function (int $value) {
                return (object) ['foo' => $value];
              } :
              '(function (int $value) { return (object) ["foo" => $value]; })'
          ),
          [12]
        ],
        (object) ['foo' => 12]
      ],
      [
        [
          (
            PHP_THREADABLE ?
              function (int $next) {
                if ($next < 3) {
                  throw new \Exception('Invalid argument');
                }

                return $next + 2;
              } :
              '(function (int $next) { if ($next < 3) { throw new Exception("Invalid argument"); } return $next + 2; })'
          ),
          [2]
        ],
        '/(Invalid argument)/i'
      ]
    ];
  }

  /**
   * @dataProvider asyncifyProvider
   */
  public function testcallRunsSynchronousPHPFunctionAsynchronously($args, $result): void
  {
    $exec = toException(
      function (...$args) {
        return await(call(...$args));
      },
      function (\Throwable $err) {
        return $err->getMessage();
      }
    )(...$args);

    if (\is_string($result)) {
      $this->assertMatchesRegularExpression(
        $result,
        $exec
      );
    } else {
      $this->assertEquals(
        $result,
        $exec
      );
    }
  }

  /**
   * @dataProvider asyncifyProvider
   */
  public function testAsynccallMethodRunsSynchronousPHPFunctionAsynchronously($args, $result): void
  {
    $exec = toException(
      function (...$args) {
        $async = Async::create();
        return await($async->call(...$args));
      },
      function (\Throwable $err) {
        return $err->getMessage();
      }
    )(...$args);

    if (\is_string($result)) {
      $this->assertMatchesRegularExpression(
        $result,
        $exec
      );
    } else {
      $this->assertEquals(
        $result,
        $exec
      );
    }
  }
}
