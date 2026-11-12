<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Models\Product;
use App\Models\Property;

/**
 * Homepage and static content pages.
 */
final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $hero = Database::first("SELECT * FROM content_sections WHERE section_key = 'home_hero' AND is_active = 1");
        $about = Database::first("SELECT * FROM content_sections WHERE section_key = 'home_about' AND is_active = 1");
        $banner = Database::first("SELECT * FROM content_sections WHERE section_key = 'promo_banner' AND is_active = 1");

        $featuredProperties = Property::featured(6);
        $featuredProducts   = Product::featured(8);
        $categories         = Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_published = 1) AS product_count
             FROM product_categories c WHERE c.is_active = 1 ORDER BY c.sort_order ASC, c.name ASC LIMIT 8"
        );
        $reviews = Database::all(
            "SELECT r.*, p.name AS product_name FROM reviews r
             LEFT JOIN products p ON p.id = r.product_id
             WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 6"
        );

        return $this->view('public/home', [
            'title'              => Settings::get('home_meta_title', 'Armstrong Homes & Interior Limited — Real Estate, Building Materials & Interior Products'),
            'metaDescription'    => Settings::get('home_meta_description', 'Armstrong Homes & Interior Limited supplies building materials, interior products and real estate solutions across Nigeria. Browse properties for sale and rent, or order building materials and interior products.'),
            'canonical'          => url('/'),
            'hero'               => $hero,
            'about'              => $about,
            'banner'             => $banner,
            'featuredProperties' => $featuredProperties,
            'featuredProducts'   => $featuredProducts,
            'categories'         => $categories,
            'reviews'            => $reviews,
        ]);
    }

    public function about(Request $request): Response
    {
        $mission = Database::first("SELECT * FROM content_sections WHERE section_key = 'about_mission' AND is_active = 1");
        $vision  = Database::first("SELECT * FROM content_sections WHERE section_key = 'about_vision' AND is_active = 1");
        $story   = Database::first("SELECT * FROM content_sections WHERE section_key = 'about_story' AND is_active = 1");

        return $this->view('public/about', [
            'title'           => 'About Us | ' . Settings::get('company_name', 'Armstrong Homes & Interior Limited'),
            'metaDescription' => 'Learn about Armstrong Homes & Interior Limited — our mission, vision, values and the two divisions we serve: real estate, and building materials & interior products.',
            'canonical'       => url('/about'),
            'mission'         => $mission,
            'vision'          => $vision,
            'story'           => $story,
        ]);
    }

    public function contact(Request $request): Response
    {
        return $this->view('public/contact', [
            'title'           => 'Contact Us | ' . Settings::get('company_name', 'Armstrong Homes & Interior Limited'),
            'metaDescription' => 'Contact Armstrong Homes & Interior Limited for property enquiries, building materials, interior products and general support.',
            'canonical'       => url('/contact'),
        ]);
    }
}
