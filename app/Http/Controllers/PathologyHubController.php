<?php

namespace App\Http\Controllers;

class PathologyHubController extends Controller
{
    /**
     * Display the pathology lab hub (home) page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pathology.index');
    }
}
