<?php namespace App\DNS\Record;

use MattyRad\Support\Conformation;

class Container implements \JsonSerializable
{
    use Conformation;

    private $emmet;
    private $theme;
    private $description;
    private $keywords;
    private $ga;
    private $favicon;
    private $title;
    private $css;
    private $inline_styles;
    private $scripts;
    private $open_graph;

    public function __construct(
        ?string $emmet = null,
        ?string $theme = null,
        ?string $description = null,
        ?string $keywords = null,
        ?string $ga = null,
        ?string $favicon = null,
        ?string $title = null,
        ?array $css = [],
        ?array $inline_styles = [],
        ?array $scripts = [],
        ?OpenGraph $open_graph = null
    ) {
        $this->emmet = $emmet;
        $this->theme = $theme;
        $this->description = $description;
        $this->keywords = $keywords;
        $this->ga = $ga;
        $this->favicon = $favicon;
        $this->title = $title;
        $this->css = $css;
        $this->inline_styles = $inline_styles;
        $this->scripts = $scripts;
        $this->open_graph = $open_graph;
    }

    public function toArray(): array
    {
        return [
            'emmet' => $this->getEmmetRecord(),
            'theme' => $this->getTheme(),
            'description' => $this->getDescription(),
            'keywords' => $this->getKeywords(),
            'ga' => $this->getGoogleAnalyticsCode(),
            'favicon' => $this->getFaviconLink(),
            'title' => $this->getTitle(),
            'css' => $this->getCssLinks(),
            'inline_styles' => $this->getInlineStyles(),
            'scripts' => $this->getScriptLinks(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function getEmmetRecord(): ?string
    {
        return $this->emmet;
    }

    public function getCssLinks(): array
    {
        return $this->css ?: [];
    }

    public function getInlineStyles(): array
    {
        return $this->inline_styles ?: [];
    }

    public function getScriptLinks(): array
    {
        return $this->scripts ?: [];
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function getFaviconLink(): ?string
    {
        return $this->favicon;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getGoogleAnalyticsCode(): ?string
    {
        return $this->ga;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getOpenGraph(): ?OpenGraph
    {
        return $this->open_graph;
    }
}
