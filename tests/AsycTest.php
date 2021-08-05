<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

\error_reporting(0);

use Chemem\Bingo\Functional as f;
use Chemem\Asyncify\Async;
use function Chemem\Asyncify\call;
use function Chemem\Asyncify\asyncify;
use function React\Promise\resolve;

class AsyncTest extends \seregazhuk\React\PromiseTesting\TestCase
{
  public function asyncifyProvider(): array
  {
    $loop = $this->eventLoop();

    return [
      // regular function
      [
        [
          $loop,
          null,
          '(fn ($x) => $x ** 2)',
          [2],
        ],
        4,
      ],
      // native PHP function
      [
        [
          $loop,
          f\filePath(0),
          'file_get_contents',
          ['foo.txt'],
        ],
        false,
      ],
      // erroneous call to native PHP function
      [
        [$loop, null, 'explode', []],
        null,
      ],
    ];
  }

  /**
   * @dataProvider asyncifyProvider
   */
  public function testasyncifyRunsSynchronousPHPFunctionAsynchronously($args, $result): void
  {
    $exec = f\toException(
      function () use ($args, $result) {
        return $this->waitForPromise(
          asyncify(...$args)->then(null, function ($_) use ($result) {
            return $result;
          }),
          (int) $GLOBALS['timeout']
        );
      },
      function () use ($result) {
        return $this->waitForPromise(
          resolve($result),
          (int) $GLOBALS['timeout']
        );
      }
    );
    $this->assertEquals($result, $exec());
  }

  public function callProvider(): array
  {
    $loop = $this->eventLoop();

    return [
      [
        [$loop],
        [
          '(fn ($x) => $x ** 2)',
          [12],
        ],
        144,
      ],
      [
        [$loop, f\filePath(0)],
        [
          'file_get_contents',
          ['foo.txt'],
        ],
        false,
      ],
    ];
  }

  /**
   * @dataProvider callProvider
   */
  public function testcallRunsAsCurryiedVersionOfasyncify($fst, $snd, $result): void
  {
    $action = call(...$fst);
    $exec   = f\toException(
      function () use ($action, $snd, $result) {
        return $this->waitForPromise(
          $action(...$snd)->then(null, function ($_) use ($result) {
            return $result;
          }),
          (int) $GLOBALS['timeout']
        );
      },
      function () use ($result) {
        return $this->waitForPromise(
          resolve($result),
          (int) $GLOBALS['timeout']
        );
      }
    );

    $this->assertInstanceOf(\Closure::class, $action);
    $this->assertEquals($result, $exec());
  }

  /**
   * @dataProvider callProvider
   */
  public function testcallMethodAsynchronouslyCallsSynchronousPHPFunction($fst, $snd, $result): void
  {
    $async  = Async::create(...$fst);
    $exec   = f\toException(
      function () use ($async, $result) {
        return $this->waitForPromise(
          $async
            ->call(...$snd)
            ->then(null, function ($_) use ($result) {
              return $result;
            }),
          (int) $GLOBALS['timeout']
        );
      },
      function () use ($result) {
        return $this->waitForPromise(
          resolve($result),
          (int) $GLOBALS['timeout']
        );
      }
    );

    $this->assertInstanceOf(Async::class, $async);
    $this->assertEquals($result, $exec());
  }

  public function errantCallProvider(): array
  {
    $loop = $this->eventLoop();

    return [
      [
        [$loop],
        [
          <<<'CODE'
          function (int $x) {
            return $x ** 2;
          }
          CODE,
          ['foo'],
        ],
        'Argument 1 passed to {closure}() must be of the type int, string given, called in Command line code on line 1',
      ],
      [
        [$loop],
        [
          <<<'CODE'
          (function (int $x) {
            if ($x < 10) {
              throw new \ValueError("Out of bounds");
            }

            return $x ** 2;
          })
          CODE,
          [4],
        ],
        'Out of bounds',
      ],
    ];
  }

  /**
   * @dataProvider errantCallProvider
   */
  public function testParserRejectsResultOfErrantFunctionCall($fst, $snd, $result): void
  {
    $async  = Async::create(...$fst);
    $final  = f\toException(
      function () use ($async, $result) {
        return $this->waitForPromise(
          $async->call(...$snd)->then(null, function ($err) {
            return $err->getMessage();
          }),
          (int) $GLOBALS['timeout'],
        );
      },
      function () use ($result) {
        return $this->waitForPromise(
          resolve($result),
          (int) $GLOBALS['timeout']
        );
      }
    );

    $this->assertPromiseRejects($async->call(...$snd));
    $this->assertEquals($result, $final());
  }
}
