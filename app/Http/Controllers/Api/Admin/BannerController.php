<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreBannerRequest;
use App\Http\Requests\Api\Admin\UpdateBannerRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::query()->orderBy('sort_order')->orderBy('id')->get();

        return BannerResource::collection($banners);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $path = $request->file('image')->store('banners', 'public');

        $banner = Banner::query()->create([
            'image_path' => $path,
            'sort_order' => $request->integer('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
            'link_url' => $request->input('link_url'),
        ]);

        return BannerResource::make($banner)->response()->setStatusCode(201);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): BannerResource
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

        return BannerResource::make($banner->fresh());
    }

    public function destroy(Banner $banner): JsonResponse
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();

        return response()->json(null, 204);
    }
}
