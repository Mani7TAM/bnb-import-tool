<?php 

namespace App\Services;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Storage;

class HotelScraper
{
    public function scrape(string $url): array
    {
        $client = HttpClient::create();
        $html = $client->request('GET', $url)->getContent();

        $crawler = new Crawler($html);

        // Example selectors - customize for target site (like Booking.com)
        $name = $crawler->filter('h2.hp__hotel-name')->first()?->text('N/A');
        $address = $crawler->filter('.hp_address_subtitle')->first()?->text('N/A');
        $description = $crawler->filter('.hotel_description_wrapper_exp')->first()?->text('N/A');

        $images = [];
        $crawler->filter('.hotel_images img')->each(function ($node, $i) use (&$images) {
            if ($i < 6) {
                $src = $node->attr('data-highres') ?: $node->attr('src');
                if ($src) {
                    $images[] = $this->storeImage($src);
                }
            }
        });

        return compact('name', 'address', 'description', 'images');
    }

    private function storeImage(string $url): string|null
    {
        try {
            $content = file_get_contents($url);
            $filename = 'hotels/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $content);
            return Storage::url($filename);
        } catch (\Exception $e) {
            return null;
        }
    }
}
