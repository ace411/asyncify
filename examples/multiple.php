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

use React\EventLoop\Factory;
use function React\Promise\all;
use Chemem\Asyncify\Async;

const BASE_URI = 'https://jsonplaceholder.typicode.com/';

$loop = Factory::create();

$async = Async::create($loop);

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

$loop->run();
