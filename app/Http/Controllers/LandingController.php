<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Landing page.
     */
    public function index(Request $request)
    {
        return view('landing');
    }
}
