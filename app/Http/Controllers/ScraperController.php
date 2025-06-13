<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HotelScraper;

class ScraperController extends Controller
{
    public function index()
    {
        return view('scraper');
    }

    public function scrape(Request $request, HotelScraper $scraper)
    {
        $data = $scraper->scrape($request->input('url'));
        return view('scraper', ['data' => $data]);
    }
}

