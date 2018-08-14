<?php


namespace Gerpo\Plugisto\Controllers;


use Gerpo\Plugisto\Models\Plugisto;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $packages = Plugisto::all();

        return view('plugisto::dashboard', compact('packages'));
    }
}