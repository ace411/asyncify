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
use React\Promise\Promise;
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
  $proc = new Process($process);
  $proc->start($loop);

  $data   = '';
  $action = function (string $chunk) use (&$data) {
    $data .= $chunk;
  };

  $proc->stdout->on('data', $action);

  return new Promise(
    function (callable $resolve, callable $reject) use (&$data, $proc) {
      $proc->stdout->on(
        'error',
        function (\Throwable $err) use ($reject) {
          $reject($err);
        }
      );

      $proc->stdout->on(
        'end',
        function () use (&$data, $resolve) {
          $resolve($data);
        }
      );
    }
  );
}
