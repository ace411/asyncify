<?php

/**
 * filepath function
 *
 * @package chemem/asyncify
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Chemem\Asyncify\Internal\Functional;

/**
 * filepath
 * outputs the absolute path to a file or directory relative to the project root
 *
 * filepath :: Int -> String -> String
 *
 * @internal
 * @param int $level
 * @param string ...$components
 * @return string
 */
function filepath(int $level, string ...$components): string
{
  return implode(
    '/',
    \array_merge(
      [\dirname(__DIR__, $level + 3)],
      $components
    )
  );
}
