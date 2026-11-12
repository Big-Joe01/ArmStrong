<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

/**
 * robots.txt and XML sitemap generation.
 */
final class SeoController extends Controller
{
    public function robots(Request $request): Response
    {
        $base = rtrim((string) Config::get('base_url', ''), '/');
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /invent',
            'Disallow: /order',
            'Disallow: /checkout',
            'Disallow: /config',
            'Disallow: /storage',
            'Disallow: /app',
            '',
            'Sitemap: ' . $base . '/sitemap.xml',
        ];
        return Response::text(implode("\n", $lines));
    }

    public function sitemap(Request $request): Response
    {
        $base = rtrim((string) Config::get('base_url', ''), '/');

        $urls = [
            ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $base . '/about', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => $base . '/contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => $base . '/real-estate', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $base . '/building-materials', 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        foreach (Database::all('SELECT slug, updated_at FROM properties WHERE is_published = 1 ORDER BY updated_at DESC LIMIT 5000') as $row) {
            $urls[] = [
                'loc'        => $base . '/real-estate/' . $row['slug'],
                'lastmod'    => date('Y-m-d', strtotime((string) $row['updated_at'])),
                'priority'   => '0.8',
                'changefreq' => 'weekly',
            ];
        }
        foreach (Database::all('SELECT slug, updated_at FROM products WHERE is_published = 1 ORDER BY updated_at DESC LIMIT 5000') as $row) {
            $urls[] = [
                'loc'        => $base . '/products/' . $row['slug'],
                'lastmod'    => date('Y-m-d', strtotime((string) $row['updated_at'])),
                'priority'   => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return new Response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
