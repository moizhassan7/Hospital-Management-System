<?php

namespace App\Http\Controllers\Lims;

use App\Http\Controllers\Controller;
use App\Models\CollectionCenter;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionCenterController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CollectionCenter::class);

        $centers = CollectionCenter::query()
            ->with('organization')
            ->orderByRaw("CASE WHEN kind = 'main_lab' THEN 0 ELSE 1 END")
            ->orderBy('code')
            ->get();

        return view('lims.collection_centers.index', compact('centers'));
    }

    public function create()
    {
        $this->authorize('create', CollectionCenter::class);

        return view('lims.collection_centers.form', [
            'center' => new CollectionCenter([
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'is_active' => true,
            ]),
            'organizations' => $this->organizations(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CollectionCenter::class);

        $data = $this->validated($request);
        CollectionCenter::query()->create($data);

        return redirect()
            ->route('pathology.collection_centers.index')
            ->with('success', 'Collection center created.');
    }

    public function edit(CollectionCenter $collectionCenter)
    {
        $this->authorize('update', $collectionCenter);

        return view('lims.collection_centers.form', [
            'center' => $collectionCenter,
            'organizations' => $this->organizations(),
        ]);
    }

    public function update(Request $request, CollectionCenter $collectionCenter)
    {
        $this->authorize('update', $collectionCenter);

        $data = $this->validated($request, $collectionCenter);
        $collectionCenter->fill($data)->save();

        return redirect()
            ->route('pathology.collection_centers.index')
            ->with('success', 'Collection center updated.');
    }

    public function toggle(CollectionCenter $collectionCenter)
    {
        $this->authorize('update', $collectionCenter);

        if ($collectionCenter->isMainLab() && $collectionCenter->is_active) {
            return back()->withErrors([
                'is_active' => 'The Main Lab site cannot be deactivated.',
            ]);
        }

        $collectionCenter->is_active = ! $collectionCenter->is_active;
        $collectionCenter->save();

        $label = $collectionCenter->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Collection center {$label}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?CollectionCenter $existing = null): array
    {
        $orgId = $request->input('organization_id')
            ?? $existing?->organization_id
            ?? Organization::query()->where('code', 'MMC')->value('id')
            ?? Organization::query()->orderBy('id')->value('id');

        $data = $request->validate([
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('collection_centers', 'code')
                    ->where(fn ($q) => $q->where('organization_id', $orgId)->whereNull('deleted_at'))
                    ->ignore($existing?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::in([
                CollectionCenter::KIND_MAIN_LAB,
                CollectionCenter::KIND_COLLECTION_CENTER,
            ])],
            'lab_number_prefix' => [
                'required',
                'string',
                'max:16',
                Rule::unique('collection_centers', 'lab_number_prefix')
                    ->where(fn ($q) => $q->where('organization_id', $orgId)->whereNull('deleted_at'))
                    ->ignore($existing?->id),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['organization_id'] = (int) ($data['organization_id'] ?? $orgId);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = strtoupper(trim($data['code']));
        $data['lab_number_prefix'] = strtoupper(trim($data['lab_number_prefix']));

        return $data;
    }

    /** @return \Illuminate\Support\Collection<int, Organization> */
    private function organizations()
    {
        return Organization::query()->orderBy('code')->get();
    }
}
