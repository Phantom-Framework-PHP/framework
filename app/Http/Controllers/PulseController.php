<?php

namespace Phantom\Http\Controllers;

use Phantom\Http\Response;

class PulseController extends Controller
{
    public function index()
    {
        $path = storage_path('framework/pulse.json');
        $data = [];

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true) ?: [];
        }

        // Si es una petición AJAX (por ejemplo para refrescar la tabla), devolvemos JSON
        if (request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return Response::json($data);
        }

        return view('phantom.pulse', ['history' => $data]);
    }

    public function clear()
    {
        $path = storage_path('framework/pulse.json');
        if (file_exists($path)) {
            unlink($path);
        }

        return redirect('/phantom/pulse');
    }
}
