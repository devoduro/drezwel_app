<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

/** Inbox for messages and wholesale enquiries sent from the website. */
class MessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->input('status', 'new') !== 'all', fn ($q) => $q->where('status', $request->input('status', 'new')))
            ->latest()->paginate(20)->withQueryString();

        return view('manager.messages', [
            'messages' => $messages,
            'counts' => ['new' => ContactMessage::where('status', 'new')->count(), 'all' => ContactMessage::count()],
        ]);
    }

    public function toggle(ContactMessage $message)
    {
        $message->update(['status' => $message->status === 'new' ? 'handled' : 'new']);

        return back()->with('success', $message->status === 'handled' ? 'Marked as handled.' : 'Marked as new.');
    }
}
