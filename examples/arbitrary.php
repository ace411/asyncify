<?php

/**
 * simple script that runs a user-defined function asynchronously and wraps its return value in a promise
 * -> works only if the arbitrary function is expressed as an anonymous function - in a string
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use React\EventLoop\Loop;

use function Chemem\Asyncify\call;

$square = <<<'CODE'
(function (int $x) {
  return $x ** 2;
})
CODE;

$call = call($square, [12])
  ->then(
    function (int $square) {
      echo $square . PHP_EOL;
    },
    function ($err) {
      echo $err->getMessage() . PHP_EOL;
    }
  );
