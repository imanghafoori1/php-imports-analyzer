<?php

namespace Imanghafoori\ImportAnalyzer;


use Imanghafoori\TokenAnalyzer\ImportsAnalyzer;

class CheckClassReferencesAreValid
{
    public static $checkWrong = true;

    public static $checkExtra = true;

    public static $extraImportsHandler = Handlers\ExtraCorrectImports::class;

    public static $extraWrongImportsHandler = Handlers\ExtraWrongImports::class;

    public static $wrongClassRefsHandler = Handlers\PrintWrongClassRefs::class;

    public static function check($tokens, $absFilePath, $imports = [])
    {
        [
            $hostNamespace,
            $extraWrongImports,
            $extraCorrectImports,
            $wrongClassRefs,
            $wrongDocblockRefs,
        ] = ImportsAnalyzer::getWrongRefs($tokens, $absFilePath, $imports);

        if (self::$checkWrong && self::$wrongClassRefsHandler) {
            self::$wrongClassRefsHandler::handle(
                array_merge($wrongClassRefs, $wrongDocblockRefs),
                $absFilePath,
                $hostNamespace,
                $tokens
            );
        }

        self::handleExtraImports($absFilePath, $extraWrongImports, $extraCorrectImports);

        return $tokens;
    }

    private static function handleExtraImports($absFilePath, $extraWrongImports, $extraCorrectImports)
    {
        // Extra wrong imports:
        if (self::$extraWrongImportsHandler) {
            self::$extraWrongImportsHandler::handle($extraWrongImports, $absFilePath);
        }

        // Extra correct imports:
        if (self::$checkExtra && self::$extraImportsHandler) {
            self::$extraImportsHandler::handle($extraCorrectImports, $absFilePath);
        }
    }
}
