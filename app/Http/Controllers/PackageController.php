<?php

namespace App\Http\Controllers;

use App\Models\Package;

class PackageController extends Controller
{
    /**
     * Show all active packages, newest first.
     */
    public function index()
    {
        $packages = Package::where('status', true)
            ->latest()
            ->get();

        return view('packages.index', compact('packages'));
    }

    /**
     * Show one package. Inactive packages are not public, so they return 404.
     */
    public function show(Package $package)
    {
        abort_unless($package->status, 404);

        return view('packages.show', compact('package'));
    }
}
