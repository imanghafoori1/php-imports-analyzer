<?php

namespace Imanghafoori\ImportAnalyzer\Reporters;

use Imanghafoori\ImportAnalyzer\ErrorCounter;
use Imanghafoori\TokenAnalyzer\ImportsAnalyzer;

class SummeryReport
{
    public static function summery($errors)
    {
        ErrorCounter::$errors = $errors;

        $messages = [];
        $messages[] = self::formatErrorSummary(ErrorCounter::getTotalErrors(), ImportsAnalyzer::$checkedRefCount);
        $messages[] = self::format('unused import', ErrorCounter::getExtraImportsCount());
        $messages[] = self::format('wrong import', ErrorCounter::getExtraWrongCount());
        $messages[] = self::format('wrong class reference', ErrorCounter::getWrongUsedClassCount());

        return implode(PHP_EOL, $messages);
    }

    public static function formatErrorSummary($totalCount, $checkedRefCount)
    {
        return '<options=bold;fg=yellow>'.$checkedRefCount.' references were checked, '.$totalCount.' error'.($totalCount == 1 ? '' : 's').' found.</>';
    }

    public static function format($errorType, $count)
    {
        return ' 🔸 <fg=yellow>'.$count.'</> '.$errorType.($count == 1 ? '' : 's').' found.';
    }
}
