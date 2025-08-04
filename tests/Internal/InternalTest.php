<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

use PHPUnit\Framework\TestCase;

use function Chemem\Asyncify\Internal\proc;
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
      [['kat --foo'], "sh: 1: kat: not found\n"],
    ];
  }

  /**
   * @dataProvider procProvider
   */
  public function testprocExecutesCommandAsynchronouslyInChildProcess($args, $result): void
  {
    $exec = null;
    try {
      $exec = await(
        proc(...$args)
      );
    } catch (\Throwable $err) {
      $exec = $err->getMessage();
    }

    $this->assertEquals($result, $exec);
  }
}
