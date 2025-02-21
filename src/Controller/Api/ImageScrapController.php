<?php

namespace App\Controller\Api;

use App\Controller\Api\input\ImageScrapRequest;
use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Service\ImageEditorService;
use App\Service\ImageScraperService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ImageScrapController extends AbstractController
{
    public const int IMAGE_SIZE = 200;

    public function __construct(
        private readonly ImageScraperService $imageScraperService,
        private readonly ImageEditorService  $imageEditorService,
        private readonly ImageRepository     $imageRepository,
    )
    {
    }

    public function __invoke(#[MapRequestPayload] ImageScrapRequest $request): Response
    {
        try {
            $images = [];
            $imageUrls = $this->imageScraperService->getImagesWithSize($request->url, $request->minWidth, $request->minHeight);

            foreach ($imageUrls as $url) {
                $image = $this->imageEditorService->makeImage($url);
                $this->imageEditorService->fit($image, self::IMAGE_SIZE);
                $this->imageEditorService->text($image, $request->text, self::IMAGE_SIZE / 2, self::IMAGE_SIZE / 2);
                $fileName = $this->imageEditorService->export($image);

                $image = new Image();
                $image->setImage($fileName);
                $images[] = $image;
                $this->imageRepository->save($image, true);
            }

            return $this->json($images, Response::HTTP_OK);
        } catch (TransportExceptionInterface $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}