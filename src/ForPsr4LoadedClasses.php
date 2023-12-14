<?php

namespace Imanghafoori\ImportAnalyzer;

use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\ImportAnalyzer\Iterators\ChecksOnPsr4Classes;

class ForPsr4LoadedClasses
{
    public static function check($checks, $params = [], $includeFile = '', $includeFolder = '')
    {
        [$stats, $exceptions] = ChecksOnPsr4Classes::apply($checks, $params, $includeFile, $includeFolder);

        foreach ($exceptions as $e) {
            self::handleClassNotFound($e);
        }

        return $stats;
    }

    private static function entityNotFound(string $msg)
    {
        return self::startsWith($msg, ['Enum ', 'Interface ', 'Class ', 'Trait ']) && self::endsWith($msg, ' not found');
    }

    private static function startsWith($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (substr($haystack, 0, strlen($needle)) === $needle) {
                return true;
            }
        }

        return false;
    }

    private static function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    private static function handleClassNotFound($e)
    {
        [$e, $filePath] = $e;

        if (! self::entityNotFound($e->getMessage())) {
            ErrorPrinter::singleton()->simplePendError($e->getMessage(), $filePath, 1, 'error', get_class($e));
        }
    }
}
