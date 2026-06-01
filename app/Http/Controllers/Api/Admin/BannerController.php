<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreBannerRequest;
use App\Http\Requests\Api\Admin\UpdateBannerRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function __construct(private MediaStorage $mediaStorage) {}

    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::query()->orderBy('sort_order')->orderBy('id')->get();

        return BannerResource::collection($banners);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $extension = $request->file('image')->guessExtension() ?: 'jpg';
        $imageUrl = $this->mediaStorage->uploadUploadedFile(
            $request->file('image'),
            'banners/'.Str::uuid().'.'.$extension
        );

        $banner = Banner::query()->create([
            'image_url' => $imageUrl,
            'sort_order' => $request->integer('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
            'link_url' => $request->input('link_url'),
        ]);

        return BannerResource::make($banner)->response()->setStatusCode(201);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): BannerResource
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

        return BannerResource::make($banner);
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $this->mediaStorage->deleteByStoredValue($banner->image_url);
        $banner->delete();

        return response()->json(null, 204);
    }
}
