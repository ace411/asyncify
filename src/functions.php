<?php

/**
 * package functions
 * 
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify;

use \React\{
  EventLoop\LoopInterface,
  Promise\PromiseInterface,
};
use Chemem\Asyncify\Internal as async;
use \Chemem\Bingo\Functional\Algorithms as f;
use function \React\Promise\{
  reject,
  resolve,
};

const asyncify = __NAMESPACE__ . '\\asyncify';

/**
 * asyncify
 * runs a synchronous PHP function asynchronously
 * -> subsumes result in a promise
 * 
 * asyncify :: Object -> String -> String -> Array -> Promise s a
 *
 * @param LoopInterface $loop
 * @param null|string $rootDir
 * @param string $func
 * @param array $args
 * @return PromiseInterface
 * @example
 * 
 * asyncify($loop, __DIR__, 'file_get_contents', ['/path/to/file'])
 * => object(React\Promise\Promise) {}
 */
function asyncify(
  LoopInterface $loop,
  ?string $rootDir,
  string $func,
  array $args
): PromiseInterface {
  // enclose function inside a f\toException call
  // serialize the resultant exception object
  $generator = f\compose(
    // convert arguments to parsable JSON format
    f\partialRight('json_encode', JSON_UNESCAPED_SLASHES),
    // enclose arguments in function parentheses
    f\partialRight(f\partial(f\concat, '', $func, '(...'), ')); }'),
    // enclose entire expression in toException block
    // -> serialize the resultant exception object
    f\partialRight(
      f\partial(f\concat, '', f\toException, '(function () { return ('),
      ', '. f\identity . ')()'
    ),
    // generate PHP code
    f\partialRight(
      f\partialRight(async\phpGenerator, 'serialize'),
      // set base directory to project root directory
      \is_null($rootDir) ? f\filePath(0) : $rootDir
    ),
    // run process
    f\partial(async\procExec, $loop)
  );
  
  return $generator($args)->then(
    function (string $chunk) {
      // check if Warning or Notice exist in message
      // -> eliminate warning and notice messages from serialized output if they do
      // -> return chunk otherwise
      $result = \unserialize(
        \preg_match('/(Notice|Warning|PHP Notice|PHP Warning){1}([\s\w\W\d]*)/', $chunk) ?
          f\last(\explode(PHP_EOL, $chunk)) :
          $chunk
      );

      return $result instanceof \Throwable ?
        reject($result) :
        resolve($result);
    }
  );
}

const call = __NAMESPACE__ . '\\call';

/**
 * call
 * curryied version of asyncify
 * -> allows users to bootstrap asynchronous function calls
 * 
 * call :: Object -> String -> (String -> Array -> Promise s a)
 *
 * @param mixed ...$args
 * @return callable
 */
function call(...$args): callable
{
  $argCount = \count($args);

  return f\curry(asyncify)(
    ...($argCount === 1 ? f\extend($args, [null]) : $args)
  );
}
