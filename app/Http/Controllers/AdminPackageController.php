<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPackageController extends Controller
{
    /**
     * List all packages (active and inactive), newest first.
     */
    public function index()
    {
        $packages = Package::latest()->get();

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the "add new package" form.
     */
    public function create()
    {
        return view('admin.packages.create');
    }

    /**
     * Validate and save a new package.
     */
    public function store(Request $request)
    {
        $data = $this->validatePackage($request);

        // Images are saved in storage/app/public/packages (path stored: packages/xxxx.jpg).
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('packages', 'public');
        }

        Package::create($data);

        return redirect('/admin/packages')->with('success', 'Package created successfully.');
    }

    /**
     * Show the edit form.
     */
    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    /**
     * Validate and update a package.
     */
    public function update(Request $request, Package $package)
    {
        $data = $this->validatePackage($request);

        $oldImage = $package->image;

        // No new file = keep the existing image.
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('packages', 'public');
        }

        $package->update($data);

        // Delete the old file only after the new one is saved.
        if ($request->hasFile('image') && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect('/admin/packages')->with('success', 'Package updated successfully.');
    }

    /**
     * Delete a package and its image file.
     */
    public function destroy(Package $package)
    {
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }

        $package->delete();

        return redirect('/admin/packages')->with('success', 'Package deleted successfully.');
    }

    /**
     * Validation shared by store() and update().
     * The returned array never contains an "image" key; controllers add it only for a new upload.
     */
    private function validatePackage(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'duration' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'image' => 'nullable|image|max:2048',
            'status' => 'boolean',
        ]);

        unset($data['image']);
        $data['status'] = $request->boolean('status');

        return $data;
    }
}
