<?php

use App\Http\Controllers\Frontend\DocumentController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\GalleryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PopulationInfographicController;
use App\Http\Controllers\Frontend\PostController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale, Request $request) {
    abort_unless(in_array($locale, ['id', 'en'], true), 404);

    $request->session()->put('locale', $locale);

    $redirectTo = $request->query('redirect');
    if (! is_string($redirectTo) || ! str_starts_with($redirectTo, url('/'))) {
        $redirectTo = url()->previous();
    }

    if (! is_string($redirectTo) || ! str_starts_with($redirectTo, url('/'))) {
        $redirectTo = route('home');
    }

    if ($redirectTo === $request->url()) {
        $redirectTo = route('home');
    }

    return redirect()->to($redirectTo);
})->name('locale.switch');

Route::get('/', HomeController::class)->name('home');

Route::get('/profil-desa', [ProfileController::class, 'index'])->name('profile.index');

Route::get('/berita', [PostController::class, 'index'])->name('posts.index');
Route::get('/berita/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/infografis-penduduk', [PopulationInfographicController::class, 'index'])->name('infographics.index');

Route::get('/agenda', [EventController::class, 'index'])->name('events.index');
Route::get('/agenda/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
Route::post('/layanan/administrasi-persuratan', [ServiceController::class, 'storeLetterSubmission'])
    ->name('services.letter-submissions.store');
Route::post('/layanan/pengaduan', [ServiceController::class, 'storeComplaintSubmission'])
    ->name('services.complaint-submissions.store');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galleries.index');
Route::get('/galeri/{gallery:slug}', [GalleryController::class, 'show'])->name('galleries.show');

Route::get('/publikasi', [DocumentController::class, 'index'])->name('documents.index');
Route::get('/publikasi/{document:slug}/download', [DocumentController::class, 'download'])->name('documents.download');
