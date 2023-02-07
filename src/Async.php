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

use function Chemem\Asyncify\Internal\asyncify;

class Async
{
  /**
   * ReactPHP event loop
   *
   * @var LoopInterface $loop
   */
  private $loop;

  /**
   * path to autoloader
   *
   * @var string $autoload
   */
  private $autoload;

  public function __construct(?string $autoload = null, ?LoopInterface $loop = null)
  {
    $this->loop     = $loop;
    $this->autoload = $autoload;
  }

  /**
   * create
   * instantiates Async object
   *
   * create :: Object -> String -> Object
   *
   * @param LoopInterface $loop
   * @param string $autoload
   * @return Async
   */
  public static function create(?string $autoload = null, ?LoopInterface $loop = null): Async
  {
    return new static($autoload, $loop);
  }

  /**
   * call
   * asynchronously calls a synchronous PHP function and subsumes result in promise
   *
   * call :: String -> Array -> Promise s a
   *
   * @param string $function
   * @param array $args
   * @return PromiseInterface
   */
  public function call(string $function, array $args): PromiseInterface
  {
    return asyncify($function, $args, $this->autoload, $this->loop);
  }
}
