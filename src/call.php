<?php

/**
 * core call function
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify;

use function Chemem\Bingo\Functional\curry;

const call = __NAMESPACE__ . '\\call';

/**
 * call
 * curryied version of asyncify
 * -> allows users to bootstrap asynchronous function calls
 *
 * call :: String -> Array -> String -> Object -> (String -> Array -> String -> Object -> Promise s a) -> Promise s a
 *
 * @param mixed ...$args
 * @return mixed
 * @example
 *
 * call('file_get_contents', ['path/to/file'])
 *  ->then(
 *    function (string $contents) {
 *      echo $contents . PHP_EOL;
 *    },
 *    function (Throwable $err) {
 *      echo $err->getMessage() . PHP_EOL;
 *    }
 *  )
 * => file_get_contents(/path/to/file): Failed to open stream: No such file or directory
 */
function call(...$args)
{
  $count = \count($args);

  return curry(Internal\asyncify)(
    ...($count === 1 ? \array_merge($args, [null]) : $args)
  );
}
