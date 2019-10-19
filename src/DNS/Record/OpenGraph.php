<?php namespace App\DNS\Record;

class OpenGraph
{
    private $title;
    private $image_link;
    private $site_name;

    public function __construct(string $title = '', string $image_link = '', string $site_name = '')
    {
        $this->title = $title;
        $this->image_link = $image_link;
        $this->site_name = $site_name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getImageLink()
    {
        return $this->image_link;
    }

    public function getSiteName()
    {
        return $this->site_name;
    }
}
