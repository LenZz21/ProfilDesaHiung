<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\PageSetting;
use App\Models\VillageProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documentsPageSetting = PageSetting::resolve(PageSetting::PAGE_DOCUMENTS);

        return view('documents.index', [
            'profile' => VillageProfile::query()->first(),
            'documentsPageSetting' => $documentsPageSetting,
            'documents' => Document::published()
                ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
                ->latest('published_at')
                ->paginate(10)
                ->withQueryString(),
            'categories' => Document::query()->whereNotNull('category')->distinct()->pluck('category'),
            'activeCategory' => $request->string('category')->toString(),
            'seoTitle' => __('Publikasi Desa'),
            'seoDescription' => __('Dokumen publikasi dan informasi resmi desa.'),
        ]);
    }

    public function download(Document $document)
    {
        abort_unless($document->status === 'published', 404);

        return Storage::disk('public')->download($document->file, $document->slug . '.' . pathinfo($document->file, PATHINFO_EXTENSION));
    }
}
