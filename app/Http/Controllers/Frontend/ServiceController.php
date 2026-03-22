<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreComplaintSubmissionRequest;
use App\Http\Requests\Frontend\StoreLetterSubmissionRequest;
use App\Models\ComplaintSubmission;
use App\Models\LetterSubmission;
use App\Models\PageSetting;
use App\Models\VillageProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ServiceController extends Controller
{
    public function index()
    {
        $profile = VillageProfile::query()->first();
        $servicesPageSetting = PageSetting::resolve(PageSetting::PAGE_SERVICES);

        return view('services.index', [
            'profile' => $profile,
            'servicesPageSetting' => $servicesPageSetting,
            'letterServices' => $this->letterServices()->all(),
            'seoTitle' => __('Layanan Desa'),
            'seoDescription' => __('Layanan digital desa untuk persuratan dan pengaduan masyarakat.'),
        ]);
    }

    public function storeLetterSubmission(StoreLetterSubmissionRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $selectedService = $this->letterServices()->firstWhere('key', $payload['service_type']);

        LetterSubmission::query()->create([
            'service_type' => $payload['service_type'],
            'service_name' => $selectedService['name'] ?? $payload['service_type'],
            'full_name' => $payload['full_name'],
            'nik' => $payload['nik'],
            'whatsapp' => $payload['whatsapp'],
            'email' => $payload['email'] ?? null,
            'purpose' => $payload['purpose'],
            'status' => LetterSubmission::STATUS_BARU,
        ]);

        return redirect()
            ->route('services.index')
            ->with('service_success', __('Permohonan surat berhasil dikirim ke admin website.'))
            ->with('active_service_tab', 'administrasi')
            ->with('active_letter_service', $payload['service_type']);
    }

    public function storeComplaintSubmission(StoreComplaintSubmissionRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        ComplaintSubmission::query()->create([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'] ?? null,
            'whatsapp' => $payload['whatsapp'],
            'complaint' => $payload['complaint'],
            'status' => ComplaintSubmission::STATUS_BARU,
        ]);

        return redirect()
            ->route('services.index')
            ->with('service_success', __('Pengaduan berhasil dikirim ke admin website.'))
            ->with('active_service_tab', 'pengaduan');
    }

    private function letterServices(): Collection
    {
        return collect(config('public_services.letter_services', []));
    }
}
