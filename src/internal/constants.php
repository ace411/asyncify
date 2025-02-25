<?php

/**
 * essential immutable library data
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify\Internal;

use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Runtime\Runtime;

/**
 * @var bool PHP_THREADABLE flag with which to ascertain presence of CSP utilities
 */
\define(
  __NAMESPACE__ . '\\PHP_THREADABLE',
  (
    \extension_loaded('parallel') &&
    \class_exists(EventLoopBridge::class) &&
    \class_exists(Runtime::class)
  )
);

/**
 * @var PHP_EXECUTABLE_TEMPLATE boilerplate for asynchronous execution of a PHP function
 */
const PHP_EXECUTABLE_TEMPLATE = <<<'PHP'
\set_error_handler(
  function (...$args) {
    [$errno, $errmsg] = $args;
    throw new \Exception($errmsg, $errno);
  },
  E_ALL
);
\set_exception_handler(
  function (Throwable $err) {
    echo $err->getMessage();
  }
);
require_once "%s";
echo \base64_encode(
  \serialize(
    %s(
      function (...$args) {
        return %s(...$args);
      },
      function ($err) {
        return new \Exception(
          $err->getMessage(),
          $err->getCode(),
          $err->getPrevious()
        );
      }
    )(...\unserialize(\base64_decode("%s")))
  )
);
PHP;
