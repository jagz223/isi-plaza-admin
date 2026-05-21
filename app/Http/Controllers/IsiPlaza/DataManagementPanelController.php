<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BannerResource;
use App\Http\Resources\Admin\BuyerResource;
use App\Http\Resources\Admin\SellerAccountResource;
use App\Models\Banner;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DataManagementPanelController extends Controller
{
    public function __invoke(): Response
    {
        $buyers = User::query()
            ->where('role', UserRole::Comprador)
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'buyers_page');

        $sellers = User::query()
            ->with(['sellerProfile.businessCategory'])
            ->where('role', UserRole::Mayorista)
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'sellers_page');

        $banners = Banner::query()->orderBy('sort_order')->orderByDesc('id')->get();

        return Inertia::render('isi-plaza/gestion', [
            'stats' => [
                'buyers_count' => User::query()->where('role', UserRole::Comprador)->count(),
                'sellers_count' => User::query()->where('role', UserRole::Mayorista)->count(),
            ],
            'buyers' => BuyerResource::collection($buyers),
            'sellers' => SellerAccountResource::collection($sellers),
            'banners' => BannerResource::collection($banners),
        ]);
    }
}
