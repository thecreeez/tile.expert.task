<?php

namespace App\Service;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\Font;
use Symfony\Component\Uid\Uuid;

class ImageEditorService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function makeImage(string $url): ImageInterface
    {
        file_put_contents('tmp', file_get_contents($url));
        return $this->imageManager->read('tmp');
    }

    public function makeLocalImage(string $path): ImageInterface
    {
        return $this->imageManager->read($path);
    }

    public function fit(ImageInterface $image): ImageInterface
    {
        $width = 200;
        $height = 200;
        return $image->resize(width: $width, height: $height);
    }

    public function text(ImageInterface $image, string $text, int $x, int $y): ImageInterface
    {
        $font = (new Font('fonts/consolas.ttf'))
            ->setColor('black')
            ->setSize(30)
            ->setAlignment('center')
            ->setValignment('middle');
        return $image->text($text, $x, $y, $font);
    }

    public function export(ImageInterface $image): string
    {
        $fileName = Uuid::v4() . '.webp';
        $image->toWebp()->save("images/$fileName");
        return $fileName;
    }
}