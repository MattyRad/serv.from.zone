<?php namespace App\Emmet;

class Expander
{
    const ABBR_LENGTH_MAX = 5000;

    public function expand(string $abbr): string
    {
        if (($len = strlen($abbr)) > self::ABBR_LENGTH_MAX) {
            throw new Exception\LengthException($len);
        }

        $output = '';
        $failed = null;

        $abbr = escapeshellarg($abbr);

        exec("echo $abbr | " . $this->getPath(), $output, $failed);

        if ($failed !== 0) {
            throw new Exception\FailedExpansion($abbr, $failed);
        }

        return implode('', $output);
    }

    private function getPath(): string
    {
        // TODO: I'm not sure how Symfony does primitive-type/config injection, just grab from env for now
        if (! $path = getenv('EMMET_PATH')) {
            throw new \InvalidArgumentException('You must supply an emmet path in your .env.local');
        }

        return $path;
    }
}
