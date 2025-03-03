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

use React\EventLoop\Loop;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Runtime\Runtime;

use function Chemem\Asyncify\Internal\Functional\curry;
use function Chemem\Asyncify\Internal\Functional\filepath;

use const Chemem\Asyncify\Internal\asyncify;
use const Chemem\Asyncify\Internal\thread;
use const Chemem\Asyncify\Internal\PHP_THREADABLE;

const call = __NAMESPACE__ . '\\call';

/**
 * call
 * curryied version of asyncify
 * -> allows users to bootstrap asynchronous function calls
 *
 * call :: Sum String (a -> b) -> Array -> Bool -> (String -> Array -> String -> Object -> Promise s b)
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
 *  );
 * => file_get_contents(/path/to/file): Failed to open stream: No such file or directory
 */
function call(...$args)
{
  if (!PHP_THREADABLE) {
    return curry(asyncify)(...$args);
  }

  // globally register runtime object
  if (!isset($GLOBALS['RUNTIME'])) {
    $GLOBALS['RUNTIME'] = new Runtime(
      new EventLoopBridge(
        $args[3] ?? Loop::get()
      ),
      $args[2] ?? filepath(0, 'vendor/autoload.php')
    );
  }

  // close runtime on shutdown
  \register_shutdown_function(
    function () {
      if (isset($GLOBALS['RUNTIME'])) {
        ($GLOBALS['RUNTIME'])->close();
        unset($GLOBALS['RUNTIME']);
      }
    }
  );

  return curry(thread)(
    ...(
      \array_merge(
        \array_slice($args, 0, 2),
        [$GLOBALS['RUNTIME']]
      )
    )
  );
}
