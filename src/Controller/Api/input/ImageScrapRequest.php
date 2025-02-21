<?php

namespace App\Controller\Api\input;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
class ImageScrapRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $url,
        public string $text,

        #[Assert\PositiveOrZero]
        public int    $minWidth = 0,
        #[Assert\PositiveOrZero]
        public int    $minHeight = 0,
    )
    {
    }
}