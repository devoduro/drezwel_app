<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\SmsService;
use App\Support\Phone;
use Illuminate\Http\Request;

/** The public website pages around the shop: home, about, contact, wholesale, delivery info and policies. */
class SiteController extends Controller
{
    private const PAGES = [
        'delivery-info' => ['Delivery & payment', 'delivery_text'],
        'return-policy' => ['Return policy', 'policy_returns'],
        'terms' => ['Terms & conditions', 'policy_terms'],
        'privacy-policy' => ['Privacy policy', 'policy_privacy'],
        'faqs' => ['FAQs', 'faq_text'],
    ];

    public function home()
    {
        $base = fn () => Product::inStore()->with(['images', 'variants'])->whereHas('variants', fn ($q) => $q->where('is_active', true));

        $categories = Product::inStore()->whereNotNull('category')->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) as n')->groupBy('category')->orderByDesc('n')->limit(8)->get()
            ->map(function ($c) use ($base) {
                $c->cover = $base()->where('category', $c->category)->get()->first(fn ($p) => $p->primary_image_url)?->primary_image_url;

                return $c;
            });

        $rows = $categories->take(4)->map(function ($c) use ($base) {
            $c->products = $base()->where('category', $c->category)->orderByRaw('stock_qty <= 0')->latest('id')->limit(12)->get();

            return $c;
        })->filter(fn ($c) => $c->products->isNotEmpty())->values();

        return view('site.home', [
            'rows' => $rows,
            'deals' => $base()->onOffer()->where('stock_qty', '>', 0)->latest('id')->limit(12)->get(),
            'new' => $base()->where('stock_qty', '>', 0)->latest('id')->limit(12)->get(),
            'categories' => $categories,
            'productCount' => Product::inStore()->count(),
        ]);
    }

    public function about()
    {
        return view('site.about');
    }

    public function page(string $slug)
    {
        abort_unless(isset(self::PAGES[$slug]), 404);
        [$title, $key] = self::PAGES[$slug];

        return view('site.page', ['title' => $title, 'body' => \App\Support\Pages::text($key), 'slug' => $slug]);
    }

    public function contact()
    {
        return view('site.contact', ['type' => 'contact']);
    }

    public function wholesale()
    {
        return view('site.contact', ['type' => 'wholesale']);
    }

    /** Stores the message and texts the managers. Used by both the contact and wholesale forms. */
    public function send(Request $request, SmsService $sms)
    {
        abort_if($request->filled('company_website'), 422); // honeypot: real visitors never see this field

        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $data = $request->validate([
            'type' => 'required|in:contact,wholesale',
            'name' => 'required|string|max:120',
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'email' => 'nullable|email|max:120',
            'subject' => 'nullable|string|max:160',
            'message' => 'required|string|min:5|max:3000',
        ], ['phone.regex' => 'Enter a 10-digit phone number, e.g. 0241234567.']);

        $msg = ContactMessage::create($data + ['ip' => $request->ip()]);

        $label = $msg->type === 'wholesale' ? 'WHOLESALE enquiry' : 'message';
        User::where('role', 'manager')->where('is_active', true)->whereNotNull('phone')->get()->each(fn ($m) => $sms->send(
            $m->phone, "Drezwel Go: new website {$label} from {$msg->name} ({$msg->phone}). Open Messages to read it.", null, 'new_message'));

        return redirect()->route($msg->type === 'wholesale' ? 'site.wholesale' : 'site.contact')
            ->with('success', 'Thank you! We have received your ' . ($msg->type === 'wholesale' ? 'wholesale enquiry' : 'message') . ' and will call you soon.');
    }

    public function sitemap()
    {
        $urls = collect([route('home'), route('shop.index'), route('site.about'), route('site.contact'), route('site.wholesale')])
            ->merge(collect(array_keys(self::PAGES))->map(fn ($s) => route('site.page', $s)))
            ->map(fn ($u) => ['loc' => $u, 'lastmod' => null]);

        $products = Product::inStore()->whereHas('variants', fn ($q) => $q->where('is_active', true))->get(['slug', 'updated_at'])
            ->map(fn ($p) => ['loc' => route('shop.product', $p->slug), 'lastmod' => $p->updated_at->toAtomString()]);

        return response()->view('site.sitemap', ['urls' => $urls->merge($products)])->header('Content-Type', 'application/xml');
    }
}
