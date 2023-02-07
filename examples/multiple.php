<?php

/**
 * simple script that runs multiple processes
 * -> uses the all() function to resolve all promises specified in list
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use React\EventLoop\Loop;
use Chemem\Asyncify\Async;

use function React\Promise\all;

const BASE_URI = 'https://jsonplaceholder.typicode.com/';

$async = Async::create();

$proc = all([
  $async->call('file_get_contents', [BASE_URI . 'todos/1']),
  $async->call('file_get_contents', [BASE_URI . 'posts/1']),
])->then(
  function (array $composite) {
    \print_r($composite);
  },
  function (\Throwable $err) {
    echo $err->getMessage();
  }
);
