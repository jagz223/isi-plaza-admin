<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreBannerRequest;
use App\Http\Requests\Api\Admin\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class BannersPanelController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('isi-plaza.gestion');
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $path = $request->file('image')->store('banners', 'public');

        Banner::query()->create([
            'image_path' => $path,
            'sort_order' => $request->integer('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
            'link_url' => $request->input('link_url'),
        ]);

        return redirect()->route('isi-plaza.gestion')->with('success', 'Banner subido.');
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        if ($request->hasFile('image')) {
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $banner->image_path = $request->file('image')->store('banners', 'public');
        }

        if ($request->has('sort_order')) {
            $banner->sort_order = $request->integer('sort_order');
        }
        if ($request->has('is_active')) {
            $banner->is_active = $request->boolean('is_active');
        }
        if ($request->has('link_url')) {
            $banner->link_url = $request->input('link_url');
        }
        $banner->save();

        return redirect()->route('isi-plaza.gestion')->with('success', 'Banner actualizado.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();

        return redirect()->route('isi-plaza.gestion')->with('success', 'Banner eliminado.');
    }
}
