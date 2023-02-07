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
use React\Promise\PromiseInterface;

use function React\Promise\reject;
use function React\Promise\Stream\buffer;

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
 * proc('php -r "echo 12;"')
 * => object(React\Promise\Promise) {}
 */
function proc(string $process, ?LoopInterface $loop = null): PromiseInterface
{
  $proc = new Process($process);
  $proc->start($loop);

  if (!$proc->stdout->isReadable()) {
    return reject(
      new \Exception(\sprintf('Could not process %s', $process))
    );
  }

  return buffer($proc->stdout);
}
