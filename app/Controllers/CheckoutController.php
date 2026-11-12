<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Settings;
use App\Core\Validator;
use App\Core\View;
use App\Services\OrderService;

/**
 * Guest checkout: order form, submission, confirmation.
 * No customer accounts, no online payment gateway.
 */
final class CheckoutController extends Controller
{
    public function form(Request $request): Response
    {
        $productId = $request->int('product', 0);
        $product   = $productId > 0
            ? Database::first('SELECT * FROM products WHERE id = ? AND is_published = 1', [$productId])
            : null;

        if ($product === null) {
            Session::flash('error', 'Please choose a product to order.');
            return $this->redirect('/building-materials');
        }

        $delivery = Database::all('SELECT * FROM delivery_rates WHERE is_active = 1 ORDER BY sort_order ASC, fee ASC');
        $images   = Database::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC LIMIT 1', [$productId]);

        return $this->view('public/checkout/form', [
            'title'           => 'Place an order | ' . Settings::get('company_name', 'Armstrong Homes & Interior Limited'),
            'metaDescription' => 'Order ' . $product['name'] . ' online. Choose store collection or delivery.',
            'canonical'       => url('/order'),
            'noindex'         => true,
            'product'         => $product,
            'mainImage'       => $images[0]['image_url'] ?? null,
            'deliveryOptions' => $delivery,
            'prefill'         => [
                'quantity' => max(1, $request->int('quantity', 1)),
                'size'     => $request->string('size'),
                'color'    => $request->string('color'),
            ],
        ]);
    }

    public function submit(Request $request): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->back($request, '/building-materials');
        }

        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('customer_name', 'Full name')->max('customer_name', 'Full name', 150)
            ->phone('phone', 'Phone number', true)
            ->email('email', 'Email address', false)
            ->required('product_id', 'Product')
            ->integer('product_id', 'Product', true, 1)
            ->integer('quantity', 'Quantity', true, 1)
            ->in('fulfilment_method', 'Fulfilment method', ['collection', 'delivery']);

        $fulfilment = $request->string('fulfilment_method', 'collection');
        if ($fulfilment === 'delivery') {
            $validator
                ->required('delivery_rate_id', 'Delivery option')
                ->required('delivery_address', 'Delivery address')->max('delivery_address', 'Delivery address', 500)
                ->required('delivery_city', 'City / town')->max('delivery_city', 'City / town', 120)
                ->required('delivery_state', 'State')->max('delivery_state', 'State', 120);
        }

        $productId = $request->int('product_id', 0);
        $quantity  = $request->int('quantity', 0);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flashInput($data);
            return $this->redirect('/order?product=' . $productId);
        }

        $options = array_filter([
            'size'  => $request->string('size') ?: null,
            'color' => $request->string('color') ?: null,
        ], static fn ($v) => $v !== null);

        try {
            $result = OrderService::create([
                'customer_name'     => (string) $data['customer_name'],
                'customer_phone'    => (string) $data['phone'],
                'customer_email'    => $request->string('email') ?: null,
                'fulfilment_method' => $fulfilment,
                'delivery_rate_id'  => $request->int('delivery_rate_id', 0),
                'delivery_address'  => $request->string('delivery_address') ?: null,
                'delivery_city'     => $request->string('delivery_city') ?: null,
                'delivery_state'    => $request->string('delivery_state') ?: null,
                'customer_note'     => $request->string('note') ?: null,
                'items'             => [[
                    'product_id' => $productId,
                    'quantity'   => $quantity,
                    'options'    => $options ?: null,
                ]],
            ], $request->ip());
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            Session::flashInput($data);
            return $this->redirect('/order?product=' . $productId);
        }

        $this->sendConfirmation($result['order_id']);

        return $this->redirect('/order/confirmation/' . $result['reference']);
    }

    public function confirmation(Request $request, string $reference): Response
    {
        $order = Database::first('SELECT * FROM orders WHERE order_reference = ?', [$reference]);
        if ($order === null) {
            return Response::html(View::render('public/errors/404', ['title' => 'Order not found']), 404);
        }

        $items = Database::all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC', [$order['id']]);
        foreach ($items as &$item) {
            $item['selected_options'] = json_decode((string) ($item['selected_options'] ?? '[]'), true) ?: [];
        }
        unset($item);

        return $this->view('public/checkout/confirmation', [
            'title'           => 'Order ' . $order['order_reference'] . ' | Order confirmation',
            'metaDescription' => 'Your order confirmation and payment instructions.',
            'canonical'       => url('/order/confirmation/' . $order['order_reference']),
            'noindex'         => true,
            'order'           => $order,
            'items'           => $items,
            'reservationDays' => (int) Config::get('reservation_days', 7),
        ]);
    }

    private function sendConfirmation(int $orderId): void
    {
        $order = Database::first('SELECT * FROM orders WHERE id = ?', [$orderId]);
        if ($order === null) {
            return;
        }
        $items = Database::all('SELECT * FROM order_items WHERE order_id = ?', [$orderId]);

        try {
            $html = View::render('emails/order_confirmation', [
                'order' => $order,
                'items' => $items,
                'reservationDays' => (int) Config::get('reservation_days', 7),
            ]);
            if (!empty($order['customer_email'])) {
                Mailer::send((string) $order['customer_email'], 'Order received - ' . $order['order_reference'], $html);
            }
            $adminEmail = (string) Settings::get('contact_email', (string) Config::get('mail.admin_email', ''));
            if ($adminEmail !== '') {
                $adminHtml = View::render('emails/admin_notification', [
                    'heading' => 'New online order ' . $order['order_reference'],
                    'lines'   => [
                        'Customer' => (string) $order['customer_name'],
                        'Phone'    => (string) $order['customer_phone'],
                        'Fulfilment' => ucfirst((string) $order['fulfilment_method']),
                        'Total'    => number_format((float) $order['total'], 2),
                    ],
                    'body'    => 'A new order has been placed and stock reserved for ' . (int) Config::get('reservation_days', 7) . ' days.',
                ]);
                Mailer::send($adminEmail, 'New order ' . $order['order_reference'], $adminHtml);
            }
        } catch (\Throwable $e) {
            // Email must never invalidate a recorded order.
            \App\Core\Logger::warning('Order confirmation email failed: ' . $e->getMessage());
        }
    }
}
