<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

use Chemem\Asyncify\Async;
use PHPUnit\Framework\TestCase;

use function Chemem\Asyncify\call;
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
      ],
      [
        [
          (
            PHP_THREADABLE ?
              function (int $val) {
                if ($next < 10) {
                  \trigger_error('Value is less than 10');

                  return $val * 2;
                }
              } :
              '(function (int $x) { if ($x < 10) { \trigger_error("Value is less than 10"); return $x; } return $x * 2; })'
          ),
          [2]
        ],
        '/(Value is less than 10)/i'
      ]
    ];
  }

  /**
   * @dataProvider asyncifyProvider
   */
  public function testcallRunsSynchronousPHPFunctionAsynchronously($args, $result): void
  {
    $exec = null;
    try {
      $exec = await(
        call(...$args)
      );
    } catch (\Throwable $err) {
      $exec = $err->getMessage();
    }

    $this->assertTrue(
      call($args[0]) instanceof \Closure
    );

    if (\is_string($result)) {
      if (PHP_VERSION_ID < 73000) {
        $this->assertTrue(
          (bool) \preg_match(
            $result,
            $exec
          )
        );
      } else {
        $this->assertMatchesRegularExpression(
          $result,
          $exec
        );
      }
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
    $exec = null;
    try {
      $async = Async::create();
      $exec  = await(
        $async->call(...$args)
      );
    } catch (\Throwable $err) {
      $exec = $err->getMessage();
    }

    if (\is_string($result)) {
      if (PHP_VERSION_ID < 73000) {
        $this->assertTrue(
          (bool) \preg_match(
            $result,
            $exec
          )
        );
      } else {
        $this->assertMatchesRegularExpression(
          $result,
          $exec
        );
      }
    } else {
      $this->assertEquals(
        $result,
        $exec
      );
    }
  }
}
