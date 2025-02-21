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

    public function fit(ImageInterface $image, int $size = 200): ImageInterface
    {
        $width = $image->width() / ($image->height() / $size);
        $height = $size;
        return $image->resize(width: $width, height: $height)->crop(200, 200, ($width - $size) / 2);
    }

    public function text(ImageInterface $image, string $text, int $x, int $y, string $color = 'white', $size = 200): ImageInterface
    {
        $fontPath = 'fonts/consolas.ttf';
        $fontSize = 16;

        do {
            $fontSize += 1;
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            $width = $bbox[2] - $bbox[0];
        } while ($width < $size * 0.75 && $fontSize < 150);

        $font = (new Font($fontPath))
            ->setColor($color)
            ->setSize($fontSize)
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