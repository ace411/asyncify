<?php

/**
 * thread function
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify\Internal;

use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Runtime\Runtime;

use function Chemem\Bingo\Functional\filePath;

const thread = __NAMESPACE__ . '\\thread';

/**
 * thread
 * executes blocking code in an arbitrary function inside a thread and conveys the result in a promise
 *
 * thread :: (a -> b) -> Array -> Object -> Promise s b
 *
 * @internal
 * @param string|callable $function
 * @param array $args
 * @param Runtime $runtime
 * @return PromiseInterface
 * @example
 *
 * $runtime = new Runtime(new EventLoopBridge());
 * $data    = thread(
 *   'file_get_contents',
 *   ['/path/to/file'],
 *   $runtime
 * );
 *
 * $data->then(
 *   function (string $contents) {
 *     echo $contents . PHP_EOL;
 *   },
 *   function (Throwable $err) {
 *     echo $err->getMessage() . PHP_EOL;
 *   }
 * );
 * => file_get_contents(/path/to/file): Failed to open stream: No such file or directory
 */
function thread(
  $function,
  array $args,
  Runtime $runtime
): PromiseInterface {
  return new Promise(
    function (callable $resolve, callable $reject) use (
      $args,
      $function,
      $runtime
    ) {
      $result = $runtime->run(
        function ($function, array $args) {
          \set_error_handler(
            function (...$args) {
              [$errno, $errmsg] = $args;

              throw new \Exception($errmsg, $errno);
            }
          );

          $result = $function(...$args);

          \restore_error_handler();

          return $result;
        },
        [$function, $args]
      );

      $resolve($result);
    }
  );
}
