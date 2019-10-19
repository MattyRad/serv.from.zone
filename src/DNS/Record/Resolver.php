<?php namespace App\DNS\Record;

use App\DNS;

class Resolver
{
    const PREFIXES = ['ga', 'favicon', 'title', 'keywords', 'description', 'theme', 'og_title', 'og_image', 'og_site_name'];
    const EXTERNAL_LINK_PREFIXES = ['css', 'script'];

    private $dig;

    public function __construct(DNS\Dig $dig)
    {
        $this->dig = $dig;
    }

    public function getRecords(string $hostname): Container
    {
        $records = $this->dig->host($hostname, ['TXT']);

        foreach ($records as $key => $record) {
            foreach (self::EXTERNAL_LINK_PREFIXES as $link_prefix) {
                if (substr($record, 0, (strlen($link_prefix) + 1)) === $link_prefix.'=') {
                    $parts = explode('=', $record, 2);

                    $value = $parts[1] ?? null;

                    if ($value) {
                        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                            ${$link_prefix}[] = $value;
                        } else {
                            $inline_styles[] = $value;
                        }

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

        $og_title = $og_title ?? '';
        $og_image = $og_image ?? '';
        $og_site_name = $og_site_name ?? '';
        $open_graph = ($og_title || $og_image || $og_site_name) ? new OpenGraph($og_title, $og_image, $og_site_name) : null;

        return Container::fromArray([
            'emmet' => $emmet,
            'theme' => $theme ?? null,
            'description' => $description ?? null,
            'keywords' => $keywords ?? null,
            'ga' => $ga ?? null,
            'favicon' => $favicon ?? null,
            'title' => $title ?? null,

            'css' => $css ?? null,
            'inline_styles' => $inline_styles ?? null,
            'scripts' => $script ?? null,
            'open_graph' => $open_graph,
        ]);
    }
}
