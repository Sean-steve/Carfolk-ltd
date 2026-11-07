<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\UploadCapacity;
use Illuminate\Http\Response;

final class DiagnosticsController extends Controller
{
    public function index(): Response
    {
        return response()
            ->view('admin.diagnostics', ['checks' => UploadCapacity::status()])
            ->header('Cache-Control', 'no-store');
    }
}
