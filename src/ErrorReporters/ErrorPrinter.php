<?php

namespace Imanghafoori\ImportAnalyzer\ErrorReporters;

use Exception;
use Imanghafoori\ImportAnalyzer\FileReaders\FilePath;
use Symfony\Component\Console\Terminal;

class ErrorPrinter
{
    public static $ignored;

    /**
     * @var array
     */
    public $errorsList = [];

    /**
     * @var int
     */
    public $total = 0;

    public $printer;

    /**
     * @var bool
     */
    public $logErrors = true;

    /**
     * @var string[]
     */
    public $pended = [];

    /**
     * @var int
     */
    public $count = 0;

    /**
     * @var self
     */
    public static $instance;

    /**
     * @return self
     */
    public static function singleton($output = null)
    {
        if (! self::$instance) {
            self::$instance = new self;
        }
        $output && (self::$instance->printer = $output);

        return self::$instance;
    }

    public function addPendingError($path, $lineNumber, $key, $header, $errorData)
    {
        if (self::isIgnored($path)) {
            return;
        }
        $this->count++;
        $this->errorsList[$key][] = (new PendingError($key))
            ->header($header)
            ->errorData($errorData)
            ->link($path, $lineNumber);
    }

    public function simplePendError($yellowText, $absPath, $lineNumber, $key, $header, $rest = '', $pre = '')
    {
        $errorData = $pre.$this->color($yellowText).$rest;

        $this->addPendingError($absPath, $lineNumber, $key, $header, $errorData);
    }

    public function color($msg)
    {
        return "<fg=blue>$msg</>";
    }

    public function print($msg, $path = '   ')
    {
        $this->printer->writeln($path.$msg);
    }

    public function printHeader($msg)
    {
        $number = ++$this->total;
        ($number < 10) && $number = " $number";

        $number = '<fg=cyan>'.$number.' </>';
        $path = "  $number";

        $width = (new Terminal)->getWidth() - 6;
        PendingError::$maxLength = max(PendingError::$maxLength, strlen($msg), $width);
        PendingError::$maxLength = min(PendingError::$maxLength, $width);
        $this->print('<fg=red>'.$msg.'</>', $path);
    }

    public function end()
    {
        $line = function ($color) {
            $this->printer->writeln(' <fg='.$color.'>'.str_repeat('_', 3 + PendingError::$maxLength).'</> ');
        };
        try {
            $line('gray');
        } catch (Exception $e) {
            $line('blue'); // for older versions of laravel
        }
    }

    public function printLink($path, $lineNumber = 4)
    {
        if ($path) {
            $this->print(self::getLink(str_replace(base_path(), '', $path), $lineNumber), '');
        }
    }

    public static function getLink($path, $lineNumber = 4)
    {
        $relativePath = FilePath::normalize(trim($path, '\\/'));

        return 'at <fg=green>'.$relativePath.'</>'.':<fg=green>'.$lineNumber.'</>';
    }

    public function logErrors()
    {
        $errList = $this->errorsList;

        foreach ($errList as $list) {
            foreach ($list as $error) {
                $this->printHeader($error->getHeader());
                $this->print($error->getErrorData());
                $this->printLink(
                    $error->getLinkPath(),
                    $error->getLinkLineNumber()
                );
                $this->end();
            }
        }

        foreach ($this->pended as $pend) {
            $this->print($pend);
            $this->end();
        }
    }

    public function getCount($key)
    {
        return \count($this->errorsList[$key] ?? []);
    }

    public function printTime()
    {
        $this->logErrors && $this->printer->writeln('time: '.round(microtime(true) - microscope_start, 3).' (sec)', 2);
    }

    /**
     * Check given path should be ignored.
     *
     * @param  string  $path
     * @return bool
     */
    public static function isIgnored($path)
    {
        $ignorePatterns = self::$ignored;

        if (! $ignorePatterns || ! is_array($ignorePatterns)) {
            return false;
        }

        foreach ($ignorePatterns as $ignorePattern) {
            if (self::is(base_path($ignorePattern), $path)) {
                return true;
            }
        }

        return false;
    }

    private static function is($pattern, $value)
    {
        if (! is_iterable($pattern)) {
            $pattern = [$pattern];
        }

        foreach ($pattern as $pattern) {
            if ($pattern === $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^'.$pattern.'\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
