<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Official;
use App\Models\OfficialStructureTitle;
use App\Models\PageSetting;
use App\Models\VillageProfile;

class ProfileController extends Controller
{
    public function index()
    {
        $profile = VillageProfile::query()->first();
        $structureTitles = OfficialStructureTitle::resolvedTitles();
        $profilePageSetting = PageSetting::resolve(PageSetting::PAGE_PROFILE);

        return view('profile.index', [
            'profile' => $profile,
            'profilePageSetting' => $profilePageSetting,
            'officials' => Official::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'structureTitles' => $structureTitles,
            'structureGroupOptions' => OfficialStructureTitle::resolvedGroupOptions(),
            'seoTitle' => __('Profil Desa'),
            'seoDescription' => __('Informasi visi misi, sejarah, peta wilayah, dan perangkat desa.'),
        ]);
    }
}
