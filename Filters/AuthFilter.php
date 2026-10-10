<?php

/**
 * app/Filters/AuthFilter.php
 *
 * Dipakai di Routes.php:
 *   filter => 'auth'           → cek sudah login (semua role)
 *   filter => 'auth:pelanggan' → semua role yang sudah login
 *   filter => 'auth:staff'     → role staff ATAU owner
 *   filter => 'auth:owner'     → role owner saja
 * 
 */

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = \Config\Services::session();
        $user    = $session->get('user');

        // Belum login sama sekali → redirect ke login
        if (empty($user['id'])) {
            return redirect()->to(site_url('login'));
        }

        $role = $user['role'] ?? '';

        if (empty($arguments)) {
            return; // lanjut
        }

        $required = $arguments[0]; // 'owner' | 'staff' | 'pelanggan'

        $allowed = match ($required) {
            'owner'     => $role === 'owner',
            'staff'     => in_array($role, ['staff', 'owner'], true),
            'pelanggan' => in_array($role, ['pelanggan', 'staff', 'owner'], true),
            default     => false,
        };

        if (!$allowed) {
            return match ($role) {
                'owner'  => redirect()->to(site_url('owner')),
                'staff'  => redirect()->to(site_url('staff')),
                default  => redirect()->to(site_url('pelanggan')),
            };
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}