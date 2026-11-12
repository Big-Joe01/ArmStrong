<?php
declare(strict_types=1);

/**
 * Demo catalogue seeder (called by scripts/install.php --seed-demo).
 * Inserts representative categories, brands, products and properties so the
 * site is not empty after installation. Idempotent: skips anything that
 * already exists by slug.
 */

use App\Core\Database;
use App\Core\Str;
use App\Services\InventoryService;

function seed_demo(): void
{
    // --- Product categories ------------------------------------------------
    $categories = [
        'Tiles' => ['Floor tiles', 'Wall tiles', 'Outdoor tiles'],
        'Sanitary ware' => ['Water closets', 'Wash basins', 'Bathtubs'],
        'Bathroom fittings' => [],
        'Kitchen fittings' => [],
        'Doors' => [],
        'Windows' => [],
        'Lighting' => [],
        'Electrical materials' => [],
        'Plumbing materials' => [],
        'Ceiling materials' => [],
        'Flooring' => [],
        'Paints and finishes' => [],
        'Interior accessories' => [],
        'Building supplies' => [],
        'Furniture and furnishings' => [],
    ];
    $categoryIds = [];
    $order = 0;
    foreach ($categories as $name => $children) {
        $slug = Str::slug($name);
        $existing = Database::first('SELECT id FROM product_categories WHERE slug = ?', [$slug]);
        if ($existing) {
            $categoryIds[$name] = (int) $existing['id'];
        } else {
            $categoryIds[$name] = Database::insert('product_categories', [
                'name' => $name, 'slug' => $slug, 'sort_order' => $order++, 'is_active' => 1,
            ]);
        }
        foreach ($children as $childName) {
            $childSlug = Str::slug($name . '-' . $childName);
            $child = Database::first('SELECT id FROM product_categories WHERE slug = ?', [$childSlug]);
            if (!$child) {
                Database::insert('product_categories', [
                    'parent_id' => $categoryIds[$name], 'name' => $childName,
                    'slug' => $childSlug, 'sort_order' => 0, 'is_active' => 1,
                ]);
            }
        }
    }

    // --- Brands ------------------------------------------------------------
    $brands = ['Generic', 'Local Made', 'Imported'];
    $brandIds = [];
    foreach ($brands as $name) {
        $slug = Str::slug($name);
        $existing = Database::first('SELECT id FROM brands WHERE slug = ?', [$slug]);
        $brandIds[$name] = $existing
            ? (int) $existing['id']
            : Database::insert('brands', ['name' => $name, 'slug' => $slug, 'is_active' => 1]);
    }

    // --- Products ----------------------------------------------------------
    $products = [
        ['60 x 60 cm Porcelain Floor Tile', 'Tiles', 'Generic', 8500.00, 'carton', 120,
            ['Size' => '60 x 60 cm', 'Finish' => 'Matt', 'Pieces per carton' => '4'], ['Grey', 'Beige'], ['60x60cm']],
        ['30 x 60 cm Ceramic Wall Tile', 'Tiles', 'Imported', 6200.00, 'carton', 80,
            ['Size' => '30 x 60 cm', 'Finish' => 'Glossy'], ['White', 'Ivory'], ['30x60cm']],
        ['Close-Coupled Water Closet', 'Sanitary ware', 'Imported', 65000.00, 'set', 25,
            ['Type' => 'Close-coupled', 'Flush' => 'Dual'], ['White'], []],
        ['Wall-Hung Wash Basin', 'Sanitary ware', 'Imported', 32000.00, 'piece', 30,
            ['Type' => 'Wall-hung', 'Material' => 'Ceramic'], ['White'], []],
        ['Rain Shower Set', 'Bathroom fittings', 'Generic', 45000.00, 'set', 18,
            ['Type' => 'Rain shower', 'Material' => 'Stainless steel'], ['Chrome'], []],
        ['Kitchen Sink (Double Bowl)', 'Kitchen fittings', 'Imported', 78000.00, 'piece', 15,
            ['Bowls' => 'Double', 'Material' => 'Stainless steel'], ['Silver'], []],
        ['Flush Panel Door', 'Doors', 'Local Made', 55000.00, 'piece', 40,
            ['Type' => 'Flush panel', 'Height' => '2100 mm'], ['Natural', 'Walnut'], []],
        ['Aluminium Sliding Window', 'Windows', 'Local Made', 42000.00, 'piece', 22,
            ['Type' => 'Sliding', 'Glazing' => 'Single'], ['Silver', 'Black'], []],
        ['LED Panel Ceiling Light', 'Lighting', 'Imported', 9500.00, 'piece', 200,
            ['Wattage' => '18 W', 'Colour temperature' => 'Cool white'], ['White'], []],
        ['PVC Ceiling Panel', 'Ceiling materials', 'Local Made', 3800.00, 'piece', 300,
            ['Width' => '250 mm', 'Length' => '5.95 m'], ['White', 'Woodgrain'], []],
        ['Emulsion Paint (20 L)', 'Paints and finishes', 'Generic', 28000.00, 'bundle', 60,
            ['Volume' => '20 L', 'Finish' => 'Matt emulsion'], ['Brilliant white'], []],
        ['PVC Pipe (4 in)', 'Plumbing materials', 'Local Made', 4500.00, 'piece', 150,
            ['Diameter' => '4 in', 'Length' => '3 m'], [], []],
    ];

    foreach ($products as [$name, $category, $brand, $price, $unit, $stock, $specs, $colors, $sizes]) {
        $slug = Str::slug($name);
        if (Database::first('SELECT id FROM products WHERE slug = ?', [$slug])) {
            continue;
        }
        $productId = Database::insert('products', [
            'category_id'       => $categoryIds[$category] ?? null,
            'brand_id'          => $brandIds[$brand] ?? null,
            'name'              => $name,
            'slug'              => $slug,
            'reference'         => 'PRD-' . strtoupper(Str::reference('', 6)),
            'short_description' => $name . ' — available for order and collection.',
            'description'       => 'A dependable ' . strtolower($name) . ' suitable for residential and commercial projects. Contact us for bulk quantities and trade pricing.',
            'unit'              => $unit,
            'price'             => number_format($price, 2, '.', ''),
            'stock_on_hand'     => 0,
            'availability'      => 'available',
            'is_published'      => 1,
            'is_featured'       => in_array($category, ['Tiles', 'Sanitary ware'], true) ? 1 : 0,
            'specs'             => json_encode($specs),
            'colors'            => $colors ? json_encode($colors) : null,
            'sizes'             => $sizes ? json_encode($sizes) : null,
            'published_at'      => date('Y-m-d H:i:s'),
        ]);
        InventoryService::initialStock($productId, $stock, null, 'Demo opening stock');
    }

    // --- Property categories ----------------------------------------------
    $propCategories = ['Residential', 'Commercial', 'Land and plots', 'Apartments', 'Houses', 'Offices', 'Shops and retail'];
    $propCategoryIds = [];
    foreach ($propCategories as $name) {
        $slug = Str::slug($name);
        $existing = Database::first('SELECT id FROM property_categories WHERE slug = ?', [$slug]);
        $propCategoryIds[$name] = $existing
            ? (int) $existing['id']
            : Database::insert('property_categories', ['name' => $name, 'slug' => $slug, 'is_active' => 1]);
    }

    // --- Properties --------------------------------------------------------
    $properties = [
        ['4-Bedroom Detached Duplex', 'Houses', 'for_sale', 85000000.00, 'total', 'Lekki', 'Lagos', 4, 4, 5, '500 sqm', '320 sqm'],
        ['3-Bedroom Apartment', 'Apartments', 'for_rent', 4500000.00, 'per_year', 'Gwarinpa', 'Abuja', 3, 3, 4, null, '180 sqm'],
        ['Serviced Office Suite', 'Offices', 'for_lease', 12000000.00, 'per_year', 'Victoria Island', 'Lagos', null, 2, 2, null, '150 sqm'],
        ['Commercial Plot (600 sqm)', 'Land and plots', 'for_sale', 35000000.00, 'total', 'Ajah', 'Lagos', null, null, null, '600 sqm', null],
        ['Retail Shop Space', 'Shops and retail', 'for_rent', 3000000.00, 'per_year', 'Wuse', 'Abuja', null, 1, 1, null, '60 sqm'],
    ];

    foreach ($properties as [$title, $category, $transaction, $price, $frequency, $city, $state, $beds, $baths, $toilets, $land, $building]) {
        $slug = Str::slug($title);
        if (Database::first('SELECT id FROM properties WHERE slug = ?', [$slug])) {
            continue;
        }
        Database::insert('properties', [
            'category_id'       => $propCategoryIds[$category] ?? null,
            'title'             => $title,
            'slug'              => $slug,
            'reference'         => 'PROP-' . strtoupper(Str::reference('', 6)),
            'short_description' => $title . ' available in ' . $city . ', ' . $state . '.',
            'description'       => 'A well-positioned ' . strtolower($title) . ' in ' . $city . ', ' . $state . '. Contact us to arrange an inspection and discuss terms.',
            'transaction_type'  => $transaction,
            'property_type'     => $category,
            'price'             => number_format($price, 2, '.', ''),
            'price_frequency'   => $frequency,
            'bedrooms'          => $beds,
            'bathrooms'         => $baths,
            'toilets'           => $toilets,
            'land_size'         => $land,
            'building_size'     => $building,
            'city'              => $city,
            'state'             => $state,
            'country'           => 'Nigeria',
            'features'          => json_encode(['Fenced', 'Gated estate', 'Treated water']),
            'amenities'         => json_encode(['24-hour security', 'Ample parking']),
            'status'            => 'available',
            'is_published'      => 1,
            'is_featured'       => in_array($category, ['Houses', 'Apartments'], true) ? 1 : 0,
            'published_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
