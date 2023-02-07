<?php

declare(strict_types=1);

namespace Chemem\Asyncify\Tests;

use Chemem\Asyncify\Async;
use PHPUnit\Framework\TestCase;

use function Chemem\Asyncify\call;
use function Chemem\Bingo\Functional\concat;
use function Chemem\Bingo\Functional\toException;
use function React\Async\await;
use function React\Promise\resolve;

class AsyncTest extends TestCase
{
  public function asyncifyProvider(): array
  {
    return [
      // invalid call to user-specified function
      [
        [
          '(function (...$args) { if (!\is_file($args[0])) { throw new \Exception("Could not find file: " . $args[0]); } return \file_get_contents($file); })',
          [12],
        ],
        'Exception: Could not find file: 12',
      ],
      // native PHP function
      [
        ['file_get_contents', ['foo.txt']],
        concat(
          ' ',
          'Error: file_get_contents(foo.txt):',
          PHP_VERSION_ID >= 80000 ? 'Failed' : 'failed',
          'to open stream: No such file or directory'
        ),
      ],
      // erroneous call to native PHP function
      [
        ['file_get_contents', []],
        concat(
          ' ',
          PHP_VERSION_ID >= 80000 ? 'Exception:' : 'Error:',
          'file_get_contents() expects at least 1',
          PHP_VERSION_ID >= 80000 ? 'argument,' : 'parameter,',
          '0 given'
        ),
      ],
      // trigger error in user-defined function
      [
        [
          '(function ($file) { if (!\is_file($file)) { trigger_error("Could not find file " . $file); } return \file_get_contents($file); })',
          ['foo.txt'],
        ],
        'Error: Could not find file foo.txt',
      ],
      // check if objects can be passed
      [
        [
          '(function (object $list) { return $list->foo; })',
          [(object)['foo' => 'foo']],
        ],
        'foo',
      ],
      // check if arrays can be passed
      [
        [
          '(function (array $list) { return $list["foo"]; })',
          [['foo' => 'foo']],
        ],
        'foo',
      ],
      // check if numbers can be passed
      [
        [
          '(function (int $x) { return $x + 10; })',
          [10],
        ],
        20,
      ],
      // check if objects can be returned
      [
        [
          '(function (string $x) { return (object)["foo" => $x]; })',
          ['foo'],
        ],
        (object)['foo' => 'foo'],
      ],
      // check if arrays can be returned
      [
        [
          '(function (string $x) { return ["foo" => $x]; })',
          ['foo'],
        ],
        ['foo' => 'foo'],
      ],
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

    $this->assertEquals($result, $exec);
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

    $this->assertEquals($result, $exec);
  }
}
