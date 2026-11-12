<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ActivityLog;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;

/**
 * Administrator authentication: login, logout, password change.
 * A single administrator account is supported; there is no registration.
 */
final class AdminAuthController extends Controller
{
    public function loginForm(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirect('invent/');
        }
        return Response::html(View::render('admin/auth/login', [
            'title' => 'Sign in',
        ], 'layouts/admin-guest'));
    }

    public function login(Request $request): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/login');
        }

        $username = $request->string('username');
        $password = $request->string('password');

        if ($username === '' || $password === '') {
            Session::flash('error', 'Enter your username and password.');
            return $this->redirect('invent/login');
        }

        [$ok, $message] = Auth::attempt($username, $password, $request->ip());

        if (!$ok) {
            Session::flash('error', $message);
            return $this->redirect('invent/login');
        }

        ActivityLog::record('login', 'administrator', Auth::id(), 'Administrator signed in.');
        Session::flash('success', 'Welcome back.');
        return $this->redirect('invent/');
    }

    public function logout(Request $request): Response
    {
        ActivityLog::record('logout', 'administrator', Auth::id(), 'Administrator signed out.');
        Auth::logout();
        Session::flash('success', 'You have been signed out.');
        return $this->redirect('invent/login');
    }

    public function passwordForm(Request $request): Response
    {
        return $this->adminView('admin/auth/password', [
            'title' => 'Change password',
            'admin' => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request): Response
    {
        if (!Csrf::check($request)) {
            Session::flash('error', 'Your session expired. Please try again.');
            return $this->redirect('invent/password');
        }

        $admin = Auth::user();
        if ($admin === null) {
            return $this->redirect('invent/login');
        }

        $data = $request->all();
        $validator = new Validator($data);
        $validator
            ->required('current_password', 'Current password')
            ->required('password', 'New password')
            ->min('password', 'New password', 12)
            ->required('password_confirmation', 'Password confirmation');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            return $this->redirect('invent/password');
        }

        if (!password_verify($request->string('current_password'), (string) $admin['password_hash'])) {
            Session::flash('error', 'Your current password is incorrect.');
            return $this->redirect('invent/password');
        }

        if ($request->string('password') !== $request->string('password_confirmation')) {
            Session::flash('error', 'The new password and confirmation do not match.');
            return $this->redirect('invent/password');
        }

        if (password_verify($request->string('password'), (string) $admin['password_hash'])) {
            Session::flash('error', 'Please choose a password different from your current one.');
            return $this->redirect('invent/password');
        }

        Database::update('administrators', [
            'password_hash'        => password_hash($request->string('password'), PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ], ['id' => $admin['id']]);

        ActivityLog::record('password_change', 'administrator', (int) $admin['id'], 'Administrator changed password.');
        Session::regenerate();
        Session::flash('success', 'Your password has been updated.');
        return $this->redirect('invent/');
    }
}
