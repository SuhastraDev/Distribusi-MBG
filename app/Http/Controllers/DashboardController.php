<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return match ($request->user()->role?->name) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('officer.dashboard'),
            'kepala_sppg' => redirect()->route('head.dashboard'),
            default => abort(403, 'Role pengguna tidak dikenali.'),
        };
    }

    public function admin(): View
    {
        return view('dashboards.admin');
    }

    public function officer(): View
    {
        return view('dashboards.officer');
    }

    public function head(): View
    {
        return view('dashboards.head');
    }
}
