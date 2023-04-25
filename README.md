<h1 align="center">asyncify</h1>

<span style="display:block;text-align:center;" align="center">

[![StyleCI](https://github.styleci.io/repos/365018048/shield?branch=master)](https://github.styleci.io/repos/365018048?branch=master)
[![asyncify CI](https://github.com/ace411/asyncify/actions/workflows/ci.yml/badge.svg)](https://github.com/ace411/asyncify/actions/workflows/ci.yml)
[![License](http://poser.pugx.org/chemem/asyncify/license)](https://packagist.org/packages/chemem/asyncify)
[![composer.lock](http://poser.pugx.org/chemem/asyncify/composerlock)](https://packagist.org/packages/chemem/asyncify)
[![Dependents](http://poser.pugx.org/chemem/asyncify/dependents)](https://packagist.org/packages/chemem/asyncify)
[![Latest Stable Version](http://poser.pugx.org/chemem/asyncify/v)](https://packagist.org/packages/chemem/asyncify)

</span>

A simple PHP library that runs your synchronous PHP functions asynchronously.

## Requirements

- PHP 7.2 or higher

## Rationale

PHP is a largely synchronous (blocking) runtime. Asynchrony - achievable via ReactPHP and other similar suites - is a potent approach to mitigating the arduousness of I/O operations that feature prominently in day-to-day programming. Melding blocking and non-blocking routines in PHP can be a tricky proposition: when attempted haphazardly, it can yield unsightly outcomes.

The impetus for creating and maintaining `asyncify` is combining blocking and non-blocking PHP. Built atop ReactPHP, `asyncify` is a tool that allows one to run blocking PHP functions in an event-driven I/O environment.

## Installation

Though it is possible to clone the repo, Composer remains the best tool for installing `asyncify`. To install the package via Composer, type the following in a console of your choosing.

```sh
$ composer require chemem/asyncify
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

- The library cannot parse closures. All executable arbitrary code should be emplaced in a string whose sole constituent is an immediately invokable anonymous function the format of which is `(function (...$args) { /* signature */ })`.

## API Reference

### Object

```php
namespace Chemem\Asyncify;

class Async {

  /* Methods */
  public static create( ?string $autoload = null [, ?React\EventLoop\LoopInterface $rootDir = null ] ) : Async;
  public function call( string $function [, array $args ] ) : React\Promise\PromiseInterface;
}
```

`Async::__construct` - Creates a new Async object instance

`Async::call` - Asynchronously calls a synchronous (blocking) PHP function

### Function

```php
namespace Chemem\Asyncify;

call ( string $func [, array $args [, ?string $autoload = null [, ?React\EventLoop\LoopInterface $args = null ] ] ] ) : React\Promise\PromiseInterface;
```

`call` - Curryied function that bootstraps asynchronous function calls

> **Note:** `asyncify` utilizes the autoload file in the root directory of the project from which it is invoked.

## Dealing with problems

Endeavor to create an issue on GitHub when the need arises or send an email to lochbm@gmail.com.

## Contributing

Consider buying me a coffee if you appreciate the offerings of the project and/or would like to provide more impetus for me to continue working on it.

<a href="https://www.buymeacoffee.com/agiroLoki" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/lato-white.png" alt="Buy Me A Coffee" style="height: 36px !important;width: 153px !important;" /></a>
