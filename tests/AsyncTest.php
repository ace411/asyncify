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
              <<<'PHP'
              (
                function (...$args) {
                  return \file_get_contents(...$args);
                }
              )
              PHP
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
          <<<'PHP'
          (
            function ($cmd) {
              return exec($cmd);
            }
          )
          PHP,
          ['echo "foo"'],
        ],
        (
          PHP_THREADABLE ?
            '/(Call to undefined function)/i' :
            '/^(foo)$/'
        )
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

    $this->assertMatchesRegularExpression(
      $result,
      $exec
    );
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

    $this->assertMatchesRegularExpression(
      $result,
      $exec
    );
  }
}
