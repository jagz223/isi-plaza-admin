<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreBannerRequest;
use App\Http\Requests\Api\Admin\UpdateBannerRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Models\Banner;
use App\Services\Banner\BannerOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function __construct(
        private MediaStorage $mediaStorage,
        private BannerOrderService $bannerOrder,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::query()
            ->with('businessCategory')
            ->orderBy('business_category_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

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
            'business_category_id' => $request->integer('business_category_id'),
            'image_url' => $imageUrl,
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active', true),
            'link_url' => $request->input('link_url'),
        ]);

        $banner->load('businessCategory');

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
            $banner->link_url = $request->input('link_url');
        }
        $banner->save();
        $banner->load('businessCategory');

        return BannerResource::make($banner);
    }

    public function moveUp(Banner $banner): BannerResource
    {
        $this->bannerOrder->move($banner, 'up');
        $banner->refresh()->load('businessCategory');

        return BannerResource::make($banner);
    }

    public function moveDown(Banner $banner): BannerResource
    {
        $this->bannerOrder->move($banner, 'down');
        $banner->refresh()->load('businessCategory');

        return BannerResource::make($banner);
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $this->mediaStorage->deleteByStoredValue($banner->image_url);
        $banner->delete();

        return response()->json(null, 204);
    }
}
