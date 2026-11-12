<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Settings;
use App\Core\Validator;
use App\Core\View;

/**
 * Public contact form and review submission.
 */
final class ContactController extends Controller
{
    public function submit(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('/contact');
        }

        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('full_name', 'Full name')->max('full_name', 'Full name', 150)
            ->email('email', 'Email address', true)
            ->phone('phone', 'Phone number', false)
            ->required('subject', 'Subject')->max('subject', 'Subject', 200)
            ->required('message', 'Message')->min('message', 'Message', 10)->max('message', 'Message', 5000);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flashInput($data);
            Session::flash('error', $validator->firstError());
            return $this->redirect('/contact');
        }

        $messageId = Database::insert('contact_messages', [
            'full_name'  => (string) $data['full_name'],
            'email'      => (string) $data['email'],
            'phone'      => $request->string('phone') ?: null,
            'subject'    => (string) $data['subject'],
            'message'    => (string) $data['message'],
            'status'     => 'unread',
            'ip_address' => $request->ip(),
        ]);

        $this->notifyAdmin($messageId);

        Session::flash('success', 'Thank you. Your message has been received and we will respond shortly.');
        return $this->redirect('/contact');
    }

    public function submitReview(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->back($request);
        }

        // Honeypot spam trap.
        if ($request->string('website') !== '') {
            Session::flash('success', 'Thank you for your review.');
            return $this->back($request);
        }

        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('customer_name', 'Your name')->max('customer_name', 'Your name', 150)
            ->integer('rating', 'Rating', true, 1)
            ->required('body', 'Review')->min('body', 'Review', 10)->max('body', 'Review', 3000)
            ->email('email', 'Email address', false);

        $rating = $request->int('rating', 0);
        if ($rating < 1 || $rating > 5) {
            $validator->addError('rating', 'Rating must be between 1 and 5.');
        }

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Session::flashInput($data);
            return $this->back($request);
        }

        $productId = $request->int('product_id', 0) ?: null;

        Database::insert('reviews', [
            'product_id'    => $productId,
            'customer_name' => (string) $data['customer_name'],
            'email'         => $request->string('email') ?: null,
            'rating'        => $rating,
            'title'         => $request->string('title') ?: null,
            'body'          => (string) $data['body'],
            'status'        => 'pending',
            'ip_address'    => $request->ip(),
        ]);

        Session::flash('success', 'Thank you. Your review has been submitted and will appear once approved.');
        return $this->back($request);
    }

    private function csrfOk(Request $request): bool
    {
        return \App\Core\Csrf::check($request);
    }

    private function notifyAdmin(int $messageId): void
    {
        $message = Database::first('SELECT * FROM contact_messages WHERE id = ?', [$messageId]);
        $to      = (string) Settings::get('contact_email', (string) Config::get('mail.admin_email', ''));
        if ($message === null || $to === '') {
            return;
        }
        try {
            $html = View::render('emails/admin_notification', [
                'heading' => 'New contact message',
                'lines'   => [
                    'Name'    => (string) $message['full_name'],
                    'Email'   => (string) $message['email'],
                    'Phone'   => (string) ($message['phone'] ?? '—'),
                    'Subject' => (string) ($message['subject'] ?? '—'),
                ],
                'body'    => (string) $message['message'],
            ]);
            Mailer::send($to, 'New contact message: ' . ($message['subject'] ?? 'Website enquiry'), $html);
        } catch (\Throwable) {
            // Non-fatal.
        }
    }
}
