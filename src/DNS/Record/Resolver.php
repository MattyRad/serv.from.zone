<?php namespace App\DNS\Record;

class Resolver
{
    const PREFIXES = ['ga', 'favicon', 'title', 'keywords', 'description', 'theme'];
    const EXTERNAL_LINK_PREFIXES = ['css', 'script'];

    public function getServRecords(string $hostname): Container
    {
        $records = $this->getRecords('serv.' . $hostname, ['TXT']);

        foreach ($records as $key => $record) {
            foreach (self::EXTERNAL_LINK_PREFIXES as $link_prefix) {
                if (substr($record, 0, (strlen($link_prefix) + 1)) === $link_prefix.'=') {
                    $parts = explode('=', $record, 2);

                    $value = $parts[1] ?? null;

                    if ($value && filter_var($value, FILTER_VALIDATE_URL) !== false) {
                        ${$link_prefix}[] = $value;
                        unset($records[$key]);
                    }
                }
            }

            foreach (self::PREFIXES as $prefix) {
                if (substr($record, 0, (strlen($prefix) + 1)) === ($prefix.'=')) {
                    $parts = explode('=', $record, 2);

                    $value = $parts[1] ?? null;

                    if ($value) {
                        ${$prefix} = $value;
                        unset($records[$key]);
                    }
                }
            }
        }

        if (count($records) > 1) {
            // TODO: warn the user that they potentially have more than 1 serv record?
        }

        $emmet = array_values($records)[0] ?? null;

        if (strlen($emmet) > 255) { // long TXT records are separated, assume quotes need to be stripped
            $emmet = str_replace('" "', '', $emmet);
        }

        return Container::fromArray([
            'emmet' => $emmet,
            'theme' => $theme ?? null,
            'description' => $description ?? null,
            'keywords' => $keywords ?? null,
            'ga' => $ga ?? null,
            'favicon' => $favicon ?? null,
            'title' => $title ?? null,

            'css' => $css ?? null,
            'scripts' => $scripts ?? null,
        ]);
    }

    private function getRecords(string $hostname, array $types): array
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
