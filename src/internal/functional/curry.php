<?php

/**
 * curry function
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify\Internal\Functional;

/**
 * curry
 * converts an uncurries function to a curried one
 *
 * curry :: ((a, b) -> c) -> Bool -> a -> b -> c
 *
 * @internal
 * @param callable $func
 * @param boolean $required
 * @return callable
 */
function curry(callable $function)
{
  $paramc = (
    new \ReflectionFunction($function)
  )
    ->getNumberOfRequiredParameters();
  $acc    = function ($args) use (
    &$acc,
    $function,
    $paramc
  ) {
    return function (...$inner) use (
      &$acc,
      $args,
      $function,
      $paramc
    ) {
      $final = \array_merge($args, $inner);

      if ($paramc <= \count($final)) {
        return $function(...$final);
      }

      return $acc($final);
    };
  };

  return $acc([]);
}
