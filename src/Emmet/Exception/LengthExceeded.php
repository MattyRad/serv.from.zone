<?php namespace App\Emmet\Exception;

use App\Emmet\Exception;

class LengthExceeded extends Exception
{
    private $length;

    public function __construct(int $length)
    {
        $this->length = $length;

        parent::__construct('Max record length was exceeded');
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
