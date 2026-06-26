<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Contracts\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreBannerRequest;
use App\Http\Requests\Api\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\Banner\BannerOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class BannersPanelController extends Controller
{
    public function __construct(
        private MediaStorage $mediaStorage,
        private BannerOrderService $bannerOrder,
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('isi-plaza.gestion');
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        if ($request->hasFile('image')) {
            $extension = $request->file('image')->guessExtension() ?: 'jpg';
            $imageUrl = $this->mediaStorage->uploadUploadedFile(
                $request->file('image'),
                'banners/'.Str::uuid().'.'.$extension
            );
        } else {
            $imageUrl = $request->string('external_image_url')->toString();
        }

        Banner::query()->create([
            'business_category_id' => $request->integer('business_category_id'),
            'image_url' => $imageUrl,
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active', true),
            'link_url' => $request->filled('link_url') ? $request->string('link_url')->toString() : null,
            'treatment_id' => $request->filled('treatment_id') ? $request->integer('treatment_id') : null,
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

        if ($request->has('business_category_id')) {
            $banner->business_category_id = $request->integer('business_category_id');
        }

        if ($request->has('sort_order')) {
            $banner->sort_order = $request->integer('sort_order');
        }
        if ($request->has('is_active')) {
            $banner->is_active = $request->boolean('is_active');
        }

        if ($request->has('link_url')) {
            $banner->link_url = $request->filled('link_url') ? $request->string('link_url')->toString() : null;
        }

        if ($request->has('treatment_id')) {
            $banner->treatment_id = $request->filled('treatment_id') ? $request->integer('treatment_id') : null;
        }

        $banner->save();

        $toggleOnly = $request->exists('is_active')
            && ! $request->hasFile('image')
            && ! $request->has('sort_order')
            && ! $request->has('business_category_id');

        $message = $toggleOnly
            ? ($banner->is_active ? 'Banner activado.' : 'Banner desactivado.')
            : 'Banner actualizado.';

        return redirect()->route('isi-plaza.gestion')->with('success', $message);
    }

    public function moveUp(Banner $banner): RedirectResponse
    {
        $this->bannerOrder->move($banner, 'up');

        return redirect()->route('isi-plaza.gestion')->with('success', 'Orden actualizado.');
    }

    public function moveDown(Banner $banner): RedirectResponse
    {
        $this->bannerOrder->move($banner, 'down');

        return redirect()->route('isi-plaza.gestion')->with('success', 'Orden actualizado.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->mediaStorage->deleteByStoredValue($banner->image_url);
        $this->bannerOrder->deleteAndCompact($banner);

        return redirect()->route('isi-plaza.gestion')->with('success', 'Banner eliminado.');
    }
}
