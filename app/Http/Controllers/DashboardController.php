<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $pages = $request->user()
            ->pages()
            ->latest()
            ->get();

        return view('pages.dashboard', compact('pages'));
    }
}
