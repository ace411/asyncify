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
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Runtime\Runtime;

use function Chemem\Asyncify\Internal\asyncify;
use function Chemem\Asyncify\Internal\thread;
use function Chemem\Bingo\Functional\extend;
use function Chemem\Bingo\Functional\filePath;

use const Chemem\Asyncify\Internal\PHP_THREADABLE;

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

  /**
   * Runtime object
   *
   * @var Runtime $runtime
   */
  private $runtime;

  public function __construct(?string $autoload = null, ?LoopInterface $loop = null)
  {
    $this->loop     = $loop;
    $this->autoload = $autoload;

    if (PHP_THREADABLE) {
      $this->runtime = new Runtime(
        new EventLoopBridge($this->loop),
        $this->autoload ?? filePath(0, 'vendor/autoload.php')
      );
    }
  }

  public function __destruct()
  {
    if (isset($this->runtime)) {
      $this->runtime->close();
    }
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
  public static function create(
    ?string $autoload     = null,
    ?LoopInterface $loop  = null
  ): Async {
    return new static($autoload, $loop);
  }

  /**
   * call
   * asynchronously calls a synchronous PHP function and subsumes result in promise
   *
   * call :: Sum String (a -> b) -> Array -> Promise s b
   *
   * @param string|callable $function
   * @param array $args
   * @return PromiseInterface
   * @example
   *
   * $async = Async::create('/path/to/autoload.php');
   * $res = $async
   *  ->call('file_get_contents', ['path/to/file'])
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
  public function call($function, array $args): PromiseInterface
  {
    $params = [$function, $args];

    return PHP_THREADABLE ?
      thread(
        ...extend(
          $params,
          [$this->runtime]
        )
      ) :
      asyncify(
        ...extend(
          $params,
          [
            $this->autoload,
            $this->loop
          ]
        )
      );
  }
}
