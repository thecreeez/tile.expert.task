<?php

namespace App\Controller\Api\input;

class ImageScrapRequest
{
    public function __construct(
        public string $url,
        public string $text,
        public int    $minWidth = 0,
        public int    $minHeight = 0,
    )
    {
    }
}