<?php namespace App\DNS;

class Dig
{
    public function host(string $hostname, array $types): array
    {
        //$records = dns_get_record('serv.' . $hostname, DNS_TXT);
        // dns_get_record appears to be broken (PHP native bug?!) for __long txt records__, use dig directly instead

        $output = [];

        $hostname = escapeshellarg($hostname);
        $types = escapeshellarg(implode(' ', $types));

        exec("dig $types +short $hostname", $output);

        $results = array_map(function ($value) {
            return trim($value, '"');
        }, $output);

        return $results;
    }
}
