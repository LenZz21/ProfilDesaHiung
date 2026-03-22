<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PageSetting;
use App\Models\Post;
use App\Models\VillageProfile;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $profile = VillageProfile::query()->first();
        $postsPageSetting = PageSetting::resolve(PageSetting::PAGE_POSTS);
        $activeCategory = trim($request->string('category')->toString());
        $searchQuery = trim($request->string('q')->toString());

        $filteredQuery = Post::published()
            ->when($activeCategory !== '', fn ($query) => $query->where('category', $activeCategory))
            ->when($searchQuery !== '', function ($query) use ($searchQuery) {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery
                        ->where('title', 'like', '%' . $searchQuery . '%')
                        ->orWhere('excerpt', 'like', '%' . $searchQuery . '%')
                        ->orWhere('content', 'like', '%' . $searchQuery . '%');
                });
            });

        $headlinePost = (clone $filteredQuery)->latest('published_at')->first();
        $postsQuery = (clone $filteredQuery)
            ->when($headlinePost, fn ($query) => $query->whereKeyNot($headlinePost->id));

        $posts = $postsQuery
            ->latest('published_at')
            ->paginate(6)
            ->appends($request->query());

        $allPublishedQuery = Post::published();
        $categories = (clone $allPublishedQuery)
            ->whereNotNull('category')
            ->orderBy('category')
            ->distinct()
            ->pluck('category');

        $totalMatched = (clone $filteredQuery)
            ->count();
        if ($headlinePost) {
            $totalMatched -= 1;
        }

        $totalMatched = max(0, $totalMatched);

        $latestPublishedAt = (clone $filteredQuery)
            ->latest('published_at')
            ->first()?->published_at;

        return view('posts.index', [
            'profile' => $profile,
            'postsPageSetting' => $postsPageSetting,
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'searchQuery' => $searchQuery,
            'headlinePost' => $headlinePost,
            'totalMatched' => $totalMatched,
            'latestPublishedAt' => $latestPublishedAt,
            'seoTitle' => __('Berita Desa'),
            'seoDescription' => __('Daftar berita terbaru dari pemerintah desa.'),
        ]);
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'published', 404);
        $profile = VillageProfile::query()->first();

        $relatedPosts = Post::published()
            ->whereKeyNot($post->id)
            ->when(filled($post->category), fn ($query) => $query->where('category', $post->category))
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($relatedPosts->count() < 3) {
            $relatedPosts = $relatedPosts->concat(
                Post::published()
                    ->whereKeyNot($post->id)
                    ->whereNotIn('id', $relatedPosts->pluck('id'))
                    ->latest('published_at')
                    ->take(3 - $relatedPosts->count())
                    ->get()
            );
        }

        return view('posts.show', [
            'profile' => $profile,
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'seoTitle' => $post->title,
            'seoDescription' => $post->excerpt ?: str(strip_tags((string) $post->content))->limit(160)->toString(),
        ]);
    }
}
