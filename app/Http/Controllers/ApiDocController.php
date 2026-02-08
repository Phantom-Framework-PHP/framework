<?php

namespace Phantom\Http\Controllers;

use Phantom\Http\Response;

class ApiDocController extends Controller
{
    public function index()
    {
        $path = base_path('public/swagger.json');
        
        if (!file_exists($path)) {
            return new Response("API Documentation not found. Please run 'php phantom api:doc' first.", 404);
        }

        return new Response(view('phantom.swagger'));
    }
}