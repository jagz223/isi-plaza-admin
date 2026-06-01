<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Contracts\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreBannerRequest;
use App\Http\Requests\Api\Admin\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class BannersPanelController extends Controller
{
    public function __construct(private MediaStorage $mediaStorage) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('isi-plaza.gestion');
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $extension = $request->file('image')->guessExtension() ?: 'jpg';
        $imageUrl = $this->mediaStorage->uploadUploadedFile(
            $request->file('image'),
            'banners/'.Str::uuid().'.'.$extension
        );

        Banner::query()->create([
            'image_url' => $imageUrl,
            'sort_order' => $request->integer('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
            'link_url' => $request->input('link_url'),
        ]);

        return redirect()->route('isi-plaza.gestion')->with('success', 'Banner subido.');
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        if ($request->hasFile('image')) {
            $this->mediaStorage->deleteByStoredValue($banner->image_url);
            $extension = $request->file('image')->guessExtension() ?: 'jpg';
            $banner->image_url = $this->mediaStorage->uploadUploadedFile(
                $request->file('image'),
                'banners/'.Str::uuid().'.'.$extension
            );
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
        $this->mediaStorage->deleteByStoredValue($banner->image_url);
        $banner->delete();

        return redirect()->route('isi-plaza.gestion')->with('success', 'Banner eliminado.');
    }
}
