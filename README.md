<h1 align="center">asyncify</h1>

<span style="display:block;text-align:center;" align="center">

[![StyleCI](https://github.styleci.io/repos/365018048/shield?branch=master)](https://github.styleci.io/repos/365018048?branch=master)
[![asyncify CI](https://github.com/ace411/asyncify/actions/workflows/ci.yml/badge.svg)](https://github.com/ace411/asyncify/actions/workflows/ci.yml)
[![License](http://poser.pugx.org/chemem/asyncify/license)](https://packagist.org/packages/chemem/asyncify)
[![Latest Stable Version](http://poser.pugx.org/chemem/asyncify/v)](https://packagist.org/packages/chemem/asyncify)
[![PHP Version Require](http://poser.pugx.org/chemem/asyncify/require/php)](https://packagist.org/packages/chemem/asyncify)

</span>

A simple library with which to run blocking I/O in a non-blocking fashion.

## Requirements

- PHP 7.2 or newer

## Rationale

PHP is home to a host of functions that condition CPU idleness between successive (serial) executions—blocking functions. The expense of blocking calls—invocations of such functions—is such that they can, when deployed haphazardly in evented systems, inflict unnecessary CPU waiting behavior whilst the kernel attempts to interleave non-blocking calls. `asyncify` is a bridge between the blocking I/O in the language userspace and the evented I/O in ReactPHP. It allows those who choose to avail themselves of it the ability to run their blocking code, with minimal plumbing, in evented systems, without derailing them.

## Installation

Though it is possible to clone the repo, Composer remains the best tool for installing `asyncify`. To install the package via Composer, type the following in a console of your choosing.

```sh
$ composer require chemem/asyncify
```

Newer versions of the library prioritize multithreading. The precondition for operationalizing multithreading is installing the [parallel](https://github.com/krakjoe/parallel) extension (`ext-parallel`) and [`react-parallel/runtime`](https://github.com/reactphp-parallel/runtime) library which can be done with the directives in the snippet below.

```sh
$ pie install pecl/parallel
$ echo "\nextension=parallel" >> "/path/to/php.ini"
$ composer require react-parallel/runtime
```

## Usage

If you want to take a Functional Programming approach, facilitated by currying, the example below should suffice.

```php
use function Chemem\Asyncify\call;

$call = call('file_get_contents', ['foo.txt'])
  ->then(
    function (?string $contents) {
      echo $contents;
    },
    function (\Throwable $err) {
      echo $err->getMessage();
    }
  );
```

### Or

If you prefer a more conventional OOP approach, the snippet below should prove apt.

```php
use Chemem\Asyncify\Async;

$exec = Async::create()
  ->call('file_get_contents', ['foo.txt'])
  ->then(
    function (?string $contents) {
      echo $contents;
    },
    function (\Throwable $err) {
      echo $err->getMessage();
    }
  );
```

The examples directory contains more nuanced uses of the library that I recommend you check out.

## Limitations

- `asyncify` is no panacea, but is capable of asynchronously executing a plethora of blocking calls. As presently constituted, the library is **incapable of processing inputs and outputs that cannot be serialized**. Its quintessential asynchronous function application primitive - `call()` - works almost exclusively with string encodings of native language functions and lambdas imported via an autoloading mechanism.

- The library, in its default configuration, cannot parse closures. All executable arbitrary code should be emplaced in a string whose sole constituent is an immediately invokable anonymous function the format of which is `(function (...$args) { /* signature */ })`.

## Multithreading

With multithreading enabled, it is possible to invoke closures and other lambdas without necessarily representing them as strings. Although string encodings are still workable, lambdas like closures should be the preferred option for representing arbitrary blocking logic. The code in the following example should work with multithreading enabled.

```php
use function Chemem\Asyncify\call;

$exec = call(
  function (...$args) {
    return \file_get_contents(...$args);
  },
  ['/path/to/file']
);

$exec->then(
  function (string $contents) {
    echo $contents;
  },
  function (\Throwable $err) {
    echo $err->getMessage();
  }
);
```

> It must be noted that string representations of lambdas (anonymous functions, closures and such) that are compatible with the default child process configuration, are not usable in versions that support multithreading.

## API Reference

### Object

```php
namespace Chemem\Asyncify;

use React\{
  EventLoop\LoopInterface,
  Promise\PromiseInterface,
};

class Async {

  /* Methods */
  public static create( ?string $autoload = null [, ?LoopInterface $rootDir = null ] ) : Async;
  public function call( string|callable $function [, array $args ] ) : PromiseInterface;
}
```

`Async::__construct` - Creates a new Async object instance

`Async::call` - Asynchronously calls a synchronous (blocking) PHP function

### Function

```php
namespace Chemem\Asyncify;

use React\{
  EventLoop\LoopInterface,
  Promise\PromiseInterface,
};

call ( string|callable $func [, array $args [, ?string $autoload = null [, ?LoopInterface $args = null ] ] ] ) : PromiseInterface;
```

`call` - Curryied function that bootstraps asynchronous function calls

### Important Considerations

- `asyncify`, by default, utilizes the autoload file (`autoload.php`) in the `vendor` directory of the composer project in which it resides.
- The library converts all errors in the functions slated for non-blocking execution to exceptions.

## Dealing with problems

Endeavor to create an issue on GitHub when the need arises or send an email to lochbm@gmail.com.

## Contributing

Consider buying me a coffee if you appreciate the offerings of the project and/or would like to provide more impetus for me to continue working on it.

<a href="https://www.buymeacoffee.com/agiroLoki" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/lato-white.png" alt="Buy Me A Coffee" style="height: 36px !important;width: 153px !important;" /></a>
