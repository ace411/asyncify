<?php

/**
 * internally process stream
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify\Internal;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

const proc = __NAMESPACE__ . '\\proc';

/**
 * proc
 * executes process asynchronously and subsumes resultant stream in a promise
 *
 * proc :: String -> Object -> Promise s a
 *
 * @internal
 * @param string $code
 * @param LoopInterface|null $loop
 * @return PromiseInterface
 * @example
 *
 * proc('php -r "echo 12 . PHP_EOL;"')
 *  ->then(
 *    function ($result) {
 *      echo $result;
 *    }
 *  )
 * => 12
 */
function proc(string $process, ?LoopInterface $loop = null): PromiseInterface
{
  $proc   = new Process($process);
  $result = new Deferred();
  $proc->start($loop);

  if (!$proc->stdout->isReadable()) {
    $result->reject(
      new \Exception(
        \sprintf('Could not process "%s"', $process)
      )
    );

    return $result;
  }

  $proc->stdout->on(
    'data',
    function ($chunk) use (&$result) {
      $result->resolve($chunk);
    }
  );

  // reject promise in the event of failure
  $proc->stdout->on(
    'error',
    function (\Throwable $err) use (&$result, &$proc) {
      $result->reject($err);
    }
  );

  // handle successful closure of the process stream
  $proc->stdout->on(
    'end',
    function () use (&$result) {
      $result->resolve(true);
    }
  );

  // handle unsuccessful closure of process stream
  $proc->stdout->on(
    'close',
    function () use (&$result, $process) {
      $result->reject(
        new \Exception(
          \sprintf('Closed process "%s"', $process)
        )
      );
    }
  );

  return $result->promise();
}
