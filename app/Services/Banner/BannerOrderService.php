<?php

namespace App\Services\Banner;

use App\Models\Banner;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BannerOrderService
{
    public function nextSortOrder(int $businessCategoryId): int
    {
        $max = Banner::query()
            ->where('business_category_id', $businessCategoryId)
            ->max('sort_order');

        return ((int) $max) + 1;
    }

    public function ensureSortOrderAvailable(int $businessCategoryId, int $sortOrder, ?int $ignoreBannerId = null): void
    {
        $exists = Banner::query()
            ->where('business_category_id', $businessCategoryId)
            ->where('sort_order', $sortOrder)
            ->when($ignoreBannerId !== null, fn ($query) => $query->where('id', '!=', $ignoreBannerId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'sort_order' => 'Ya existe un banner en este rubro con ese orden.',
            ]);
        }
    }

    public function move(Banner $banner, string $direction): void
    {
        if (! in_array($direction, ['up', 'down'], true)) {
            return;
        }

        $neighborQuery = Banner::query()
            ->where('business_category_id', $banner->business_category_id);

        $neighbor = $direction === 'down'
            ? (clone $neighborQuery)
                ->where('sort_order', '>', $banner->sort_order)
                ->orderBy('sort_order')
                ->first()
            : (clone $neighborQuery)
                ->where('sort_order', '<', $banner->sort_order)
                ->orderByDesc('sort_order')
                ->first();

        if ($neighbor === null) {
            return;
        }

        DB::transaction(function () use ($banner, $neighbor): void {
            $bannerOrder = $banner->sort_order;
            $neighborOrder = $neighbor->sort_order;
            $tempOrder = Banner::query()
                ->where('business_category_id', $banner->business_category_id)
                ->max('sort_order') + 1000;

            $banner->sort_order = $tempOrder;
            $banner->save();

            $neighbor->sort_order = $bannerOrder;
            $neighbor->save();

            $banner->sort_order = $neighborOrder;
            $banner->save();
        });
    }

    public function deleteAndCompact(Banner $banner): void
    {
        DB::transaction(function () use ($banner): void {
            $categoryId = $banner->business_category_id;
            $deletedOrder = $banner->sort_order;

            $banner->delete();

            Banner::query()
                ->where('business_category_id', $categoryId)
                ->where('sort_order', '>', $deletedOrder)
                ->decrement('sort_order');
        });
    }
}
