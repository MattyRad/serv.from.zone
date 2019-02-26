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
    private $scripts;

    public function __construct(
        ?string $emmet = null,
        ?string $theme = null,
        ?string $description = null,
        ?string $keywords = null,
        ?string $ga = null,
        ?string $favicon = null,
        ?string $title = null,
        ?array $css = [],
        ?array $scripts = []
    ) {
        $this->emmet = $emmet;
        $this->theme = $theme;
        $this->description = $description;
        $this->keywords = $keywords;
        $this->ga = $ga;
        $this->favicon = $favicon;
        $this->title = $title;
        $this->css = $css;
        $this->scripts = $scripts;
    }

    public function toArray(): array
    {
        return [
            'emmet' => $this->emmet,
            'theme' => $this->theme,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'ga' => $this->ga,
            'favicon' => $this->favicon,
            'title' => $this->title,
            'css' => $this->css,
            'scripts' => $this->scripts,
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
}
