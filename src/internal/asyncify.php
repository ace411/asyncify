<?php

/**
 * core library function
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify\Internal;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

use function Chemem\Bingo\Functional\concat;
use function Chemem\Bingo\Functional\filePath;
use function Chemem\Bingo\Functional\head;
use function Chemem\Bingo\Functional\partial;
use function React\Promise\reject;
use function React\Promise\resolve;

use const Chemem\Bingo\Functional\toException;

const asyncify = __NAMESPACE__ . '\\asyncify';

/**
 * asyncify
 * runs a synchronous PHP function asynchronously and subsumes the result in a promise
 *
 * asyncify :: String -> Array -> String ->  Object -> Promise s a
 *
 * @param string $function
 * @param array $args
 * @param string|null $autoload
 * @param LoopInterface|null $loop
 * @return PromiseInterface
 * @example
 *
 * $data = asyncify('file_get_contents', ['path/to/file'])
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
function asyncify(
  string $function,
  array $args,
  ?string $autoload     = null,
  ?LoopInterface $loop  = null
): PromiseInterface {
  return proc(
    \sprintf(
      'php -r \'%s\'',
      \preg_replace(
        ['/(\s){2,}/', '/(\\n)/'],
        '',
        \sprintf(
          PHP_EXECUTABLE_TEMPLATE,
          // path to autoloader
          $autoload ?? filePath(0, 'vendor/autoload.php'),
          // composable exception handler
          toException,
          // format inline functions
          $function,
          // utilize only array values as arguments
          \base64_encode(
            \serialize(
              \array_values($args)
            )
          )
        )
      )
    ),
    $loop
  )
    ->then(
      function (?string $result) {
        $data = \unserialize(
          \base64_decode($result)
        );

        if ($data instanceof \Throwable) {
          return reject(
            new \Exception(
              $data->getMessage()
            )
          );
        }

        return resolve($data);
      }
    );
}
