<?php

namespace Gerpo\Plugisto\Controllers;

use Illuminate\Routing\Controller;
use Gerpo\Plugisto\Models\Plugisto;

class DashboardController extends Controller
{
    public function index()
    {
        $packages = Plugisto::all();

        return view('plugisto::dashboard', compact('packages'));
    }
}
