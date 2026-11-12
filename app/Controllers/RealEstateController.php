<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Settings;
use App\Core\Validator;
use App\Models\Property;

/**
 * Real estate division: listings, search, details, enquiries.
 */
final class RealEstateController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q'                => $request->string('q'),
            'category'         => $request->string('category'),
            'transaction_type' => $request->string('type'),
            'state'            => $request->string('state'),
            'city'             => $request->string('city'),
            'min'              => $request->input('min'),
            'max'              => $request->input('max'),
            'bedrooms'         => $request->input('bedrooms'),
            'sort'             => $request->string('sort', 'featured'),
        ];

        $result     = Property::paginate($filters, max(1, $request->int('page', 1)), 9);
        $categories = Database::all('SELECT * FROM property_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');

        return $this->view('public/real-estate/index', [
            'title'           => 'Real Estate | ' . Settings::get('company_name', 'Armstrong Homes & Interior Limited'),
            'metaDescription' => 'Browse residential and commercial properties, land, apartments, houses, offices and shops for sale, rent or lease in Nigeria.',
            'canonical'       => url('/real-estate'),
            'filters'         => $filters,
            'result'          => $result,
            'categories'      => $categories,
            'states'          => Property::states(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $property = Property::findBySlug($slug);
        if ($property === null) {
            return Response::html(\App\Core\View::render('public/errors/404', ['title' => 'Property not found']), 404);
        }

        Database::run('UPDATE properties SET views = views + 1 WHERE id = ?', [$property['id']]);

        $images = Property::images((int) $property['id']);
        $related = Property::related((int) $property['id'], isset($property['category_id']) ? (int) $property['category_id'] : null, 3);

        $features   = json_decode((string) ($property['features'] ?? '[]'), true) ?: [];
        $amenities  = json_decode((string) ($property['amenities'] ?? '[]'), true) ?: [];
        $specs      = json_decode((string) ($property['specifications'] ?? '[]'), true) ?: [];

        return $this->view('public/real-estate/show', [
            'title'           => (string) ($property['meta_title'] ?: ($property['title'] . ' | Real Estate')),
            'metaDescription' => (string) ($property['meta_description'] ?: \App\Core\Str::excerpt((string) $property['short_description'], 155)),
            'canonical'       => url('/real-estate/' . $property['slug']),
            'ogImage'         => $images[0]['image_url'] ?? null,
            'property'        => $property,
            'images'          => $images,
            'related'         => $related,
            'features'        => $features,
            'amenities'       => $amenities,
            'specs'           => $specs,
        ]);
    }

    public function enquire(Request $request, string $slug): Response
    {
        $property = Property::findBySlug($slug);
        if ($property === null) {
            Session::flash('error', 'Property not found.');
            return $this->redirect('/real-estate');
        }

        if (!\App\Core\Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('/real-estate/' . $slug);
        }

        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('full_name', 'Full name')->max('full_name', 'Full name', 150)
            ->phone('phone', 'Phone number', true)
            ->email('email', 'Email address', false)
            ->required('message', 'Message')->min('message', 'Message', 5)->max('message', 'Message', 3000);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flashInput($data);
            return $this->redirect('/real-estate/' . $slug . '#enquire');
        }

        $enquiryId = Database::insert('property_enquiries', [
            'property_id' => (int) $property['id'],
            'full_name'   => (string) $data['full_name'],
            'email'       => $request->string('email') ?: null,
            'phone'       => (string) $data['phone'],
            'message'     => (string) $data['message'],
            'status'      => 'new',
            'ip_address'  => $request->ip(),
        ]);

        $this->notifyAdmin($enquiryId, $property);

        Session::flash('success', 'Thank you. Your enquiry about "' . $property['title'] . '" has been received. Our team will contact you shortly.');
        return $this->redirect('/real-estate/' . $slug . '#enquire');
    }

    private function notifyAdmin(int $enquiryId, array $property): void
    {
        $enquiry = Database::first('SELECT * FROM property_enquiries WHERE id = ?', [$enquiryId]);
        $to = (string) Settings::get('contact_email', '');
        if ($enquiry === null || $to === '') {
            return;
        }
        try {
            $html = \App\Core\View::render('emails/admin_notification', [
                'heading' => 'New property enquiry',
                'lines'   => [
                    'Property' => (string) $property['title'],
                    'Name'     => (string) $enquiry['full_name'],
                    'Phone'    => (string) $enquiry['phone'],
                    'Email'    => (string) ($enquiry['email'] ?? '—'),
                ],
                'body'    => (string) ($enquiry['message'] ?? ''),
            ]);
            Mailer::send($to, 'Property enquiry: ' . $property['title'], $html);
        } catch (\Throwable) {
            // Non-fatal.
        }
    }
}
