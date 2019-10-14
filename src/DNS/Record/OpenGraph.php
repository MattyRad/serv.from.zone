<?php namespace App\DNS\Record;

use MattyRad\Support\Conformation;

class OpenGraph implements \JsonSerializable
{
    use Conformation;

    private $title;
    private $image_link;

    public function __construct(string $title = '', string $image_link = '')
    {
        $this->title = $title;
        $this->image_link = $image_link;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getImageLink()
    {
        return $this->image_link;
    }
}
