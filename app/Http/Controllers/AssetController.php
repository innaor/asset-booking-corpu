<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Subcategory;

class AssetController extends Controller
{
    // List aset
    public function index()
    {
        $assets = Asset::with('subcategory.category')->get();
        $categories = Category::all();
        $subcategories = Subcategory::all(); //  TAMBAH INI

        return view('admin.assets.index', compact('assets', 'categories', 'subcategories'));
    }

    // Form tambah
    public function create()
    {
        $categories = Category::all();
        $subcategories = Subcategory::all();

        return view('admin.assets.create', compact('categories', 'subcategories'));
    }

    // Simpan aset
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('assets')->where(function ($query) use ($request) {
                    return $query->where('subcategory_id', $request->subcategory_id);
                }),
            ],
            'subcategory_id' => 'required',
        ], [
            'name.unique' => 'Aset dengan nama ini sudah ada di Jenis Aset yang dipilih.',
        ]);

        Asset::create([
            'name' => $request->name,
            'subcategory_id' => $request->subcategory_id,
            'status' => 'draft'
        ]);

        return redirect('/admin/assets')->with('success', 'Aset berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('assets')->where(function ($query) use ($request) {
                    return $query->where('subcategory_id', $request->subcategory_id);
                })->ignore($id),
            ],
            'subcategory_id' => 'required',
        ], [
            'name.unique' => 'Aset dengan nama ini sudah ada di subkategori yang dipilih.',
        ]);

        $asset = Asset::findOrFail($id);

        $asset->update([
            'name' => $request->name,
            'subcategory_id' => $request->subcategory_id
        ]);

        return back()->with('success', 'Aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);

        $asset->delete();

        return back()->with('success', 'Aset berhasil dihapus.');
    }

    // Publish / Unpublish
    public function toggleStatus($id)
    {
        $asset = Asset::findOrFail($id);

        $asset->status = $asset->status == 'draft' ? 'published' : 'draft';

        $asset->save();

        return back();
    }

    public function storeSubcategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'subcategory_name' => [
                'required',
                Rule::unique('subcategories')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                }),
            ],
        ], [
            'subcategory_name.unique' => 'Jenis Aset dengan nama ini sudah ada di kategori yang dipilih.',
        ]);

        Subcategory::create([
            'category_id' => $request->category_id,
            'subcategory_name' => $request->subcategory_name
        ]);

        return back()->with('success', 'Jenis Aset berhasil ditambahkan');
    }

}