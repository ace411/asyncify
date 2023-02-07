<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

use PHPUnit\Framework\TestCase;

use function Chemem\Asyncify\Internal\proc;
use function Chemem\Bingo\Functional\toException;
use function React\Async\await;

class InternalTest extends TestCase
{
  public function procProvider(): array
  {
    return [
      // commandline process
      [['echo foo'], "foo\n"],
      // php commandline process
      [['php -r \'echo "foo";\''], 'foo'],
      // invalid input
      [['kat --foo'], ''],
    ];
  }

  /**
   * @dataProvider procProvider
   */
  public function testprocExecutesCommandAsynchronouslyInChildProcess($args, $result): void
  {
    $exec = toException(
      function (...$args) {
        return await(proc(...$args));
      },
      function ($err) {
        return $err->getMessage();
      }
    )(...$args);

    $this->assertEquals($result, $exec);
  }
}
