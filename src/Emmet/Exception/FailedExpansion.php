<?php namespace App\Emmet\Exception;

use App\Emmet\Exception;

class FailedExpansion extends Exception
{
    public function __construct(string $abbr, $exec_code)
    {
        parent::__construct("Failed to expand $abbr, failed return " . gettype($exec_code) . ' ' . $exec_code);
    }
}
