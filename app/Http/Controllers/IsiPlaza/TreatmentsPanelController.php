<?php

namespace App\Http\Controllers\IsiPlaza;

use App\Http\Controllers\Controller;
use App\Http\Requests\IsiPlaza\StoreTreatmentRequest;
use App\Http\Requests\IsiPlaza\StoreTreatmentSectionRequest;
use App\Http\Requests\IsiPlaza\UpdateTreatmentRequest;
use App\Http\Requests\IsiPlaza\UpdateTreatmentSectionRequest;
use App\Models\Treatment;
use App\Models\TreatmentSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TreatmentsPanelController extends Controller
{
    public function index(): Response
    {
        $sections = TreatmentSection::query()
            ->with(['treatments' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (TreatmentSection $section) => [
                'id' => $section->id,
                'name' => $section->name,
                'slug' => $section->slug,
                'sort_order' => $section->sort_order,
                'is_active' => $section->is_active,
                'treatments' => $section->treatments->map(fn (Treatment $treatment) => [
                    'id' => $treatment->id,
                    'name' => $treatment->name,
                    'slug' => $treatment->slug,
                    'sort_order' => $treatment->sort_order,
                    'is_active' => $treatment->is_active,
                ])->values(),
            ]);

        return Inertia::render('isi-plaza/tratamientos', [
            'sections' => $sections,
        ]);
    }

    public function storeSection(StoreTreatmentSectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $slug = Str::slug($validated['name']);

        TreatmentSection::query()->create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSectionSlug($slug),
            'sort_order' => $validated['sort_order'] ?? ((int) TreatmentSection::query()->max('sort_order')) + 1,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Sección creada correctamente.');
    }

    public function updateSection(UpdateTreatmentSectionRequest $request, TreatmentSection $section): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $section->name = $validated['name'];
            if ($request->boolean('update_slug')) {
                $section->slug = $this->uniqueSectionSlug(Str::slug($validated['name']), $section->id);
            }
        }

        if (array_key_exists('sort_order', $validated)) {
            $section->sort_order = $validated['sort_order'];
        }

        if (array_key_exists('is_active', $validated)) {
            $section->is_active = $validated['is_active'];
        }

        $section->save();

        return back()->with('success', 'Sección actualizada.');
    }

    public function destroySection(TreatmentSection $section): RedirectResponse
    {
        $section->delete();

        return back()->with('success', 'Sección eliminada.');
    }

    public function storeTreatment(StoreTreatmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $slug = Str::slug($validated['name']);

        Treatment::query()->create([
            'treatment_section_id' => $validated['treatment_section_id'],
            'name' => $validated['name'],
            'slug' => $this->uniqueTreatmentSlug($validated['treatment_section_id'], $slug),
            'sort_order' => $validated['sort_order'] ?? ((int) Treatment::query()
                ->where('treatment_section_id', $validated['treatment_section_id'])
                ->max('sort_order')) + 1,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Tratamiento creado.');
    }

    public function updateTreatment(UpdateTreatmentRequest $request, Treatment $treatment): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $treatment->name = $validated['name'];
            if ($request->boolean('update_slug')) {
                $treatment->slug = $this->uniqueTreatmentSlug(
                    $treatment->treatment_section_id,
                    Str::slug($validated['name']),
                    $treatment->id,
                );
            }
        }

        if (array_key_exists('treatment_section_id', $validated)) {
            $treatment->treatment_section_id = $validated['treatment_section_id'];
        }

        if (array_key_exists('sort_order', $validated)) {
            $treatment->sort_order = $validated['sort_order'];
        }

        if (array_key_exists('is_active', $validated)) {
            $treatment->is_active = $validated['is_active'];
        }

        $treatment->save();

        return back()->with('success', 'Tratamiento actualizado.');
    }

    public function destroyTreatment(Treatment $treatment): RedirectResponse
    {
        $treatment->delete();

        return back()->with('success', 'Tratamiento eliminado.');
    }

    private function uniqueSectionSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'seccion';
        $candidate = $slug;
        $i = 2;

        while (TreatmentSection::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = "{$slug}-{$i}";
            $i++;
        }

        return $candidate;
    }

    private function uniqueTreatmentSlug(int $sectionId, string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'tratamiento';
        $candidate = $slug;
        $i = 2;

        while (Treatment::query()
            ->where('treatment_section_id', $sectionId)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = "{$slug}-{$i}";
            $i++;
        }

        return $candidate;
    }
}
