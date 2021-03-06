<?php

/**
 * simple script that uses asyncify to read a file's contents
 * -> wraps around PHP's native `file_get_contents` function
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use React\EventLoop\Loop;
use function Chemem\Asyncify\call;

$call = call(Loop::get());

$proc = $call('file_get_contents', [])
  ->then(
    function (?string $result) {
      \var_dump($result);
    },
    function (\Throwable $err) {
      echo $err->getMessage();
    }
  );
