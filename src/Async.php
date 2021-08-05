<?php

/**
 * core library object
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

class Async
{
  /**
   * ReactPHP event loop
   *
   * @var LoopInterface $loop
   */
  private $loop;

  /**
   * project root directory
   *
   * @var string $rootDir
   */
  private $rootDir;

  public function __construct(LoopInterface $loop, ?string $rootDir = null)
  {
    $this->loop    = $loop;
    $this->rootDir = $rootDir;
  }

  /**
   * create
   * instantiates Async object
   *
   * create :: Object -> String -> Object
   *
   * @param LoopInterface $loop
   * @param string $rootDir
   * @return Async
   */
  public static function create(LoopInterface $loop, ?string $rootDir = null): Async
  {
    return new static($loop, $rootDir);
  }

  /**
   * call
   * asynchronously calls a synchronous PHP function
   * -> returns result subsumed in promise
   *
   * call :: String -> Array -> Promise s a
   *
   * @param string $function
   * @param array $args
   * @return PromiseInterface
   */
  public function call(string $function, array $args): PromiseInterface
  {
    return asyncify($this->loop, $this->rootDir, $function, $args);
  }
}
