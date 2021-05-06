<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

\error_reporting(0);

use Chemem\Bingo\Functional\Algorithms as f;
use function Chemem\Asyncify\Internal\{
  procExec,
  phpGenerator,
};

class InternalTest extends \seregazhuk\React\PromiseTesting\TestCase
{
  public function procExecProvider(): array
  {
    $loop = $this->eventLoop();

    return [
      // commandline process
      [
        [$loop, 'echo foo'],
        'foo',
      ],
      // php commandline process
      [
        [$loop, phpGenerator(f\filePath(0), f\identity . '(12)')],
        12,
      ],
    ];
  }

  /**
   * @dataProvider procExecProvider
   */
  public function testprocExecExecutesChildProcessAsynchronously($args, $result): void
  {
    $this->assertTrueAboutPromise(procExec(...$args), function ($exec) use ($result) {
      return $exec === $result;
    });
  }

  public function phpGeneratorProvider(): array
  {
    return [
      [
        [f\filePath(0), f\identity . '(12)'],
        'php -r \'require "' . f\filePath(0) . '/vendor/autoload.php"; echo json_encode(' . f\identity . '(12));\'',
      ],
      [
        [f\filePath(0), 'file_get_contents("file.txt")', 'serialize'],
        'php -r \'require "' . f\filePath(0) . '/vendor/autoload.php"; echo serialize(file_get_contents("file.txt"));\'',
      ],
    ];
  }

  /**
   * @dataProvider phpGeneratorProvider
   */
  public function testphpGeneratorGeneratesPHPDirectiveWithSpecifiedFunctionCall($args, $result): void
  {
    $exec = phpGenerator(...$args);

    $this->assertEquals($result, $exec);
    $this->assertIsString($exec);
  }
}