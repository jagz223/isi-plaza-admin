<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\SyncDoctorServicesRequest;
use App\Http\Resources\Seller\DoctorServiceResource;
use App\Http\Resources\Seller\SellerAccountResource;
use App\Models\DoctorService;
use App\Models\SellerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $profile = $this->profileOrFail($request);

        $services = $profile->doctorServices()
            ->with(['treatment.section'])
            ->get();

        return response()->json([
            'data' => DoctorServiceResource::collection($services),
        ]);
    }

    public function sync(SyncDoctorServicesRequest $request): SellerAccountResource
    {
        $user = $request->user();
        $profile = SellerProfile::query()->firstOrCreate(['user_id' => $user->id]);

        DB::transaction(function () use ($profile, $request): void {
            $incoming = collect($request->validated('services'));
            $treatmentIds = $incoming->pluck('treatment_id')->all();

            $profile->doctorServices()
                ->whereNotIn('treatment_id', $treatmentIds)
                ->delete();

            foreach ($incoming as $service) {
                DoctorService::query()->updateOrCreate(
                    [
                        'seller_profile_id' => $profile->id,
                        'treatment_id' => $service['treatment_id'],
                    ],
                    ['price' => $service['price']],
                );
            }
        });

        return SellerAccountResource::make(
            $user->fresh()->load([
                'sellerProfile.businessCategory',
                'sellerProfile.catalogImages',
                'sellerProfile.doctorServices.treatment.section',
            ])
        );
    }

    private function profileOrFail(Request $request): SellerProfile
    {
        $profile = $request->user()->sellerProfile;

        abort_if($profile === null, 404, 'Perfil no encontrado.');

        return $profile;
    }
}
