<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Pages;
use Illuminate\Http\Request;

/** Website pages (policies, FAQs, about, shop introduction) written and edited by the manager. */
class PageController extends Controller
{
    public function index()
    {
        $pages = collect(Pages::ALL)->map(fn ($p, $slug) => [
            'slug' => $slug, 'title' => $p[0], 'help' => $p[2], 'draft' => Pages::usesDraft($p[1]), 'url' => Pages::url($slug),
        ])->values();

        return view('manager.pages.index', ['pages' => $pages]);
    }

    public function edit(string $slug)
    {
        $page = Pages::find($slug);
        abort_unless($page, 404);

        return view('manager.pages.edit', [
            'slug' => $slug, 'title' => $page[0], 'help' => $page[2], 'key' => $page[1],
            'body' => Pages::text($page[1]), 'draft' => Pages::draft($page[1]), 'usingDraft' => Pages::usesDraft($page[1]),
            'url' => Pages::url($slug),
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $page = Pages::find($slug);
        abort_unless($page, 404);
        $data = $request->validate(['body' => 'nullable|string|max:30000']);

        // An empty box means "use the suggested draft again".
        Setting::set($page[1], trim((string) ($data['body'] ?? '')));

        return redirect()->route('manager.pages.edit', $slug)->with('success', "{$page[0]} saved.");
    }
}
