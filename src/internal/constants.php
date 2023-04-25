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

/**
 * @var PHP_EXECUTABLE_TEMPLATE boilerplate for asynchronous execution of a PHP function
 */
const PHP_EXECUTABLE_TEMPLATE = <<<'PHP'
function handleException(Throwable $exception): void
{
  echo $exception->getMessage();
}
function handleError(...$args)
{
  throw new \Exception($args[1]);
}
\set_error_handler("handleError", E_ALL);
\set_exception_handler("handleException");
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
