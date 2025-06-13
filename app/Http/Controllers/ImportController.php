<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('import-form');
    }

    public function handleForm(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        // $response = Http::withHeaders([
        //     'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        // ])->get($request->input('url'));
        
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
        ])->get($request->input('url'));

        $html = $response->body();
        $crawler = new Crawler($html);

        // Name
    // Name
$name = $crawler->filter('h2[data-testid="title"], h2.pp-header__title, h2.hp__hotel-name, h2')->first()?->text('N/A');

// Description
$description = $crawler->filter('div[data-testid="property-description"], #property_description, .hotel_description_wrapper_exp, .hotel_description')->first()?->text('N/A');

// Address
// $address = $crawler->filter('span[data-node_tt_id="location_score_tooltip"], .hp_address_subtitle, .address, span.hp_address_subtitle')->first()?->text('N/A');
$address = $crawler->filterXPath('//*[@id="wrap-hotelpage-top"]/div[3]/div/div/span[1]/button/div/text()')->first()?->text('N/A');
        // Coordinates
        if (strpos($html, 'geo.position') === false && strpos($html, 'booking_com:location:coordinates') === false) {
            $coordinates = [];
        }
        else{
            $coordinates = $crawler->filter('meta[property="booking_com:location:coordinates"], meta[name="geo.position"]')->first()?->attr('content');
        }

        // Rating
        // $rating = $crawler->filter('[data-testid="review-score-component"] [aria-label], .bui-review-score__badge')->first()?->text('N/A');
        $rating = $crawler->filterXPath('//*[@id="js--hp-gallery-scorecard"]/a/div/div/div/div[2]')->first()?->text('N/A');

        // Review Count
        // $reviewCount = $crawler->filter('[data-testid="review-score-component"] .bui-review-score__text, .bui-review-score__text')->first()?->text('N/A');
        $reviewCount = $crawler->filterXPath('//*[@id="js--hp-gallery-scorecard"]/a/div/div/div/div[4]/div[2]')->first()?->text('N/A');

        // Room Types & Prices
        $roomTypes = [];
        $crawler->filter('.hprt-table tr')->each(function ($node) use (&$roomTypes) {
            $type = $node->filter('.hprt-roomtype-icon-link, .hprt-roomtype-link')->count()
                ? $node->filter('.hprt-roomtype-icon-link, .hprt-roomtype-link')->text('N/A')
                : null;
            $price = $node->filter('.hprt-price-price-standard, .prco-valign-middle-helper')->count()
                ? $node->filter('.hprt-price-price-standard, .prco-valign-middle-helper')->text('N/A')
                : null;
            if ($type && $price) {
                $roomTypes[] = [
                    'type' => trim($type),
                    'price' => trim($price),
                ];
            }
        });

        // Images (3–6 from gallery)
        $images = [];
        $crawler->filter('div[data-testid="image-gallery-thumbnail"] img, .bh-photo-grid-thumb-cell img')->each(function ($node) use (&$images) {
            if (count($images) >= 6) return;
            $src = $node->attr('data-highres') ?: $node->attr('src');
            if ($src && str_contains($src, 'booking.com')) {
                try {
                    $contents = file_get_contents($src);
                    $filename = 'hotels/' . uniqid() . '.jpg';
                    Storage::disk('public')->put($filename, $contents);
                    $images[] = 'storage/' . $filename;
                } catch (\Exception $e) {
                    // skip failed images
                }
            }
        });

        // Fallback: If not enough images, try all images
        if (count($images) < 3) {
            $crawler->filter('img')->each(function ($node) use (&$images) {
                if (count($images) >= 6) return;
                $src = $node->attr('data-highres') ?: $node->attr('src');
                if ($src && str_contains($src, 'booking.com') && !in_array($src, $images)) {
                    try {
                        $contents = file_get_contents($src);
                        $filename = 'hotels/' . uniqid() . '.jpg';
                        Storage::disk('public')->put($filename, $contents);
                        $images[] = 'storage/' . $filename;
                    } catch (\Exception $e) {}
                }
            });
        }

        return view('review-listing', compact(
            'name', 'description', 'address', 'coordinates', 'rating', 'reviewCount', 'roomTypes', 'images'
        ));  
    }

    public function saveListing(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'address' => 'required|string',
            'coordinates' => 'required|string',
            'rating' => 'nullable|string',
            'reviewCount' => 'nullable|integer',
            'roomTypes' => 'required|array',
            'images' => 'required|array',
        ]);

        $listing = Listing::create([
            'name' => trim($request->name),
            'description' => trim($request->description),
            'address' => trim($request->address),
            'coordinates' => trim($request->coordinates),
            'rating' => $request->rating,
            'review_count' => $request->reviewCount,
            'room_types' => json_encode($request->roomTypes),
            'images' => json_encode($request->images),
        ]);

        return redirect()->route('import.form')->with('success', 'Imported: ' . $listing->name);
    }
}