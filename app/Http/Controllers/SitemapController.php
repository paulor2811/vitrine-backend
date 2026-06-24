<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        private readonly SitemapService $sitemapService,
    ) {}

    public function index(): Response
    {
        return response($this->sitemapService->generate(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $apiUrl      = rtrim(config('app.url'), '/');

        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            "Sitemap: {$apiUrl}/sitemap.xml",
            '',
            "Host: {$frontendUrl}",
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
