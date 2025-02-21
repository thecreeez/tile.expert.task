<?php

namespace App\Service;

use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ImageScraperService
{
    public function __construct(
        private HttpClientInterface $client,
        private TranslatorInterface $translator,
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function getImagesWithSize(string $url, int $width, int $height, ?string $locale = 'en'): array
    {
        $response = $this->client->request(
            'GET',
            $url
        );

        if ($response->getStatusCode() != 200) {
            throw new Exception($response->getContent(false));
        }

        $contentType = $this->getContentType($response);
        if ($contentType !== 'text/html') {
            throw new Exception($this->translator->trans('errors.scrap.contentType', [
                'contentType' => $contentType,
            ], null, $locale));
        }

        $images = [];
        $crawler = new Crawler($response->getContent(), $url);
        $crawler->filter('img')->each(function (Crawler $node, $i) use (&$images, $url, $width, $height) {
            $source = $node->attr('src');

            if (!$source) {
                return;
            }
            
            if (str_ends_with($this->getUrlWithoutQuery($source), '.svg')) {
                return;
            }

            if (str_starts_with($source, '/')) {
                $source = $this->getHostname($url) . $source;
            }

            $imageSize = getimagesize($source);
            if ($imageSize[0] < $width && $imageSize[1] < $height) {
                return;
            }
            $images[] = $source;
        });
        return $images;
    }

    private function getUrlWithoutQuery(string $url): string
    {
        return explode('?', $url)[0];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getContentType(ResponseInterface $response): string
    {
        return explode(';', $response->getHeaders(false)['content-type'][0])[0];
    }

    private function getHostname(string $url): string
    {
        $result = parse_url($url);
        return $result['scheme'] . "://" . $result['host'];
    }
}