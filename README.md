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
use React\EventLoop\Loop;
use function Chemem\Asyncify\call;

$call = call(Loop::get(), __DIR__);

$exec = $call('file_get_contents', ['foo.txt'])->then(
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
use React\EventLoop\Loop;
use Chemem\Asyncify\Async;

$exec = Async::create(Loop::get())
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

Though both examples in the antecedent text utilize the new default loop, the old standard with the `Factory::create()` and `$loop->run()` artifacts is still supported.

**Note:** The examples directory contains more nuanced uses of the library that I recommend you check out.

## API Reference

### Object

```php
class Async {

  /* Methods */
  public static create( LoopInterface $loop [, ?string $rootDir = null ] ) : Async;
  public function call( string $function [, array $args ] ) : PromiseInterface;
}
```

`Async::__construct` - Creates a new Async object instance

`Async::call` - Asynchronously calls a synchronous (blocking) PHP function

### Function

```php
call ( mixed ...$args ) : callable

asyncify ( LoopInterface $loop [, ?string $rootDir [, string $func [, array $args ] ] ] ) : PromiseInterface
```

`call` - Curryied version of asyncify (bootstraps asynchronous function calls)

`asyncify` - Runs a synchronous PHP function asynchronously

## Dealing with problems

Endeavor to create an issue on GitHub when the need arises or send an email to lochbm@gmail.com.

## Contributing

Consider buying me a coffee if you appreciate the offerings of the project and/or would like to provide more impetus for me to continue working on it.

<a href="https://www.buymeacoffee.com/agiroLoki" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/lato-white.png" alt="Buy Me A Coffee" style="height: 36px !important;width: 153px !important;" /></a>
