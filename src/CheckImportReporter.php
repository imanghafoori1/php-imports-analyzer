<?php

namespace Imanghafoori\ImportAnalyzer;

use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\TokenAnalyzer\ImportsAnalyzer;

class CheckImportReporter
{
    public static function printErrorsCount()
    {
        $printer = ErrorPrinter::singleton();
        $wrongUsedClassCount = count($printer->errorsList['wrongClassRef'] ?? []);
        $extraCorrectImportsCount = count($printer->errorsList['extraCorrectImport'] ?? []);
        $extraWrongImportCount = count($printer->errorsList['extraWrongImport'] ?? []);

        $wrongCount = $extraWrongImportCount;
        $extraImportsCount = $extraCorrectImportsCount + $extraWrongImportCount;
        $totalErrors = $wrongUsedClassCount + $extraCorrectImportsCount + $extraWrongImportCount;

        $output = '<options=bold;fg=yellow>'.ImportsAnalyzer::$checkedRefCount.' references were checked, '.$totalErrors.' error'.($totalErrors == 1 ? '' : 's').' found.</>'.PHP_EOL;
        $output .= ' - <fg=yellow>'.$extraImportsCount.' unused</> import'.($extraImportsCount == 1 ? '' : 's').' found.'.PHP_EOL;
        $output .= ' - <fg=red>'.$wrongCount.' wrong</> import'.($wrongCount <= 1 ? '' : 's').' found.'.PHP_EOL;
        $output .= ' - <fg=red>'.$wrongUsedClassCount.' wrong</> class reference'.($wrongUsedClassCount <= 1 ? '' : 's').' found.';

        return $output;
    }

    public static function printPsr4(array $psr4Stats)
    {
        $spaces = self::getMaxLength($psr4Stats);
        $result = '';
        foreach ($psr4Stats as $composerPath => $psr4) {
            $composerPath = trim($composerPath, '/');
            $composerPath = $composerPath ? trim($composerPath, '/').'/' : '';
            $output = ' <fg=blue>./'.$composerPath.'composer.json'.'</>'.PHP_EOL;
            foreach ($psr4 as $psr4Namespace => $psr4Paths) {
                foreach ($psr4Paths as $path => $countClasses) {
                    $countClasses = str_pad((string) $countClasses, 3, ' ', STR_PAD_LEFT);
                    $len = strlen($psr4Namespace);
                    $output .= '   - <fg=red>'.$psr4Namespace.str_repeat(' ', $spaces - $len).' </>';
                    $output .= " <fg=blue>$countClasses </>file".($countClasses == 1 ? '' : 's').' found (<fg=green>./'.$path."</>)\n";
                }
            }
            $result .= $output.PHP_EOL;
        }

        return $result;
    }

    private static function getMaxLength(array $psr4Stats)
    {
        $lengths = [1];
        foreach ($psr4Stats as $psr4) {
            foreach ($psr4 as $psr4Namespace => $psr4Paths) {
                $lengths[] = strlen($psr4Namespace);
            }
        }

        return max($lengths);
    }

    public static function totalImportsMsg()
    {
        return '<options=bold;fg=yellow>'.ImportsAnalyzer::$checkedRefCount.' imports were checked under:</>';
    }
}
