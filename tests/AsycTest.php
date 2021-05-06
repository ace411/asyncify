<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

\error_reporting(0);

use Chemem\Bingo\Functional\Algorithms as f;
use Chemem\Asyncify\Async;
use function Chemem\Asyncify\{
  call,
  asyncify,
};

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
    $this->assertTrueAboutPromise(asyncify(...$args), function ($exec) use ($result) {
      return $exec === $result;
    });
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

    $this->assertInstanceOf(\Closure::class, $action);
    $this->assertTrueAboutPromise($action(...$snd), function ($exec) use ($result) {
      return $exec === $result;
    });
  }

  /**
   * @dataProvider callProvider
   */
  public function testcallMethodAsynchronouslyCallsSynchronousPHPFunction($fst, $snd, $result): void
  {
    $async = Async::create(...$fst);

    $this->assertInstanceOf(Async::class, $async);
    $this->assertTrueAboutPromise($async->call(...$snd), function ($exec) use ($result) {
      return $exec === $result;
    });
  }
}
