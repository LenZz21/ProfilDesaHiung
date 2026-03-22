<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\PageSetting;

class GalleryController extends Controller
{
    public function index()
    {
        $galleriesPageSetting = PageSetting::resolve(PageSetting::PAGE_GALLERIES);

        return view('galleries.index', [
            'galleriesPageSetting' => $galleriesPageSetting,
            'galleries' => Gallery::query()->latest()->paginate(9),
            'seoTitle' => __('Galeri Desa'),
            'seoDescription' => __('Album foto kegiatan dan dokumentasi desa.'),
        ]);
    }

    public function show(Gallery $gallery)
    {
        return view('galleries.show', [
            'gallery' => $gallery,
            'seoTitle' => $gallery->title,
            'seoDescription' => $gallery->description ?: __('Detail album galeri desa.'),
        ]);
    }
}
