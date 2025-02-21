<?php

namespace App\Serializer;

use App\Entity\Image;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class ImageNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,

        private StorageInterface    $storage
    )
    {
    }

    public function normalize($data, string $format = null, array $context = []): array
    {
        /* @var Image $data */
        $data->setImage($this->storage->resolveUri($data, 'imageFile'));

        return $this->normalizer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Image;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Image::class => true,
        ];
    }
}