<?php

/**
 * internal processing functions
 * -> power asyncify's more notable functions
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify\Internal;

use React\Promise\Stream;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Chemem\Bingo\Functional as f;
use function React\Promise\reject;
use function React\Promise\resolve;

const procExec = __NAMESPACE__ . '\\procExec';

/**
 * procExec
 * executes asynchronous process and wraps the result in a promise
 *
 * procExec :: Object -> String -> Promise s a
 *
 * @internal
 * @param LoopInterface $loop
 * @param string $code
 * @return PromiseInterface
 * @example
 *
 * procExec($loop, 'php -r "echo 12;"')
 * => object(React\Promise\Promise) {}
 */
function procExec(LoopInterface $loop, string $code): PromiseInterface
{
  $proc = new Process($code);
  $proc->start($loop);

  $result = $proc->stdout->on('data', f\identity);

  // convert process stream result to promise
  return Stream\buffer($result);
}

const phpGenerator = __NAMESPACE__ . '\\phpGenerator';

/**
 * phpGenerator
 * generates executable PHP directive with specified function call
 *
 * phpGenerator :: String -> String -> String -> String
 *
 * @internal
 * @param string $rootDir
 * @param string $func
 * @param string $print
 * @return string
 * @example
 *
 * phpGenerator('path/to/vendor/dir', 'identity(12)')
 * => php -r 'require "/root/vendor/autoload.php"; echo json_encode(identity(12));'
 */
function phpGenerator(
  string $rootDir,
  string $func,
  string $print = 'json_encode'
): string {
  $generator = f\compose(
    // replace first semicolon
    // f\partial('str_replace', ';', ''),
    // enclose the expressions in parentheses
    f\partialRight(f\partial(f\concat, '', $print, '('), ')'),
    // print the output via the echo directive
    f\partialRight(f\partial(f\concat, '', 'echo '), ';'),
    // replace {expr} with the expression to evaluate
    f\partialRight(
      f\partial('str_replace', '{expr}'),
      'require \'{root}/vendor/autoload.php\'; {expr}'
    ),
    // replace all single quotes with double quotes
    f\partial('str_replace', '\'', '"'),
    // enclose expression in single quotes
    f\partialRight(f\partial(f\concat, '', '\''), '\''),
    // append executable PHP code to php -r command
    f\partial(f\concat, ' ', 'php', '-r'),
    // replace carriage returns and new line characters with spaces
    f\partial('str_replace', PHP_EOL, ' '),
    // replace {baseDir} with a path
    f\partial('str_replace', '{root}', $rootDir)
  );

  return $generator($func);
}
