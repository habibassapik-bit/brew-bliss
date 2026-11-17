<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Tampilkan ruang bantuan (percakapan customer dengan customer service).
     */
    public function index(): View
    {
        $conversation = Conversation::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->first();

        if ($conversation === null || $conversation->status === 'closed') {
            $conversation = auth()->user()->conversations()->create([
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        $conversation->messages()->where('sender_role', 'admin')->whereNull('read_at')->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

        return view('chat.index', [
            'conversation' => $conversation,
            'messages' => $messages,
            'storeHours' => Setting::get('store_open_hours', '-'),
        ]);
    }

    /**
     * Kirim pesan dari customer ke ruang bantuannya sendiri.
     */
    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_if($conversation->user_id !== auth()->id(), 404);
        abort_if($conversation->status === 'closed', 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'sender_role' => 'customer',
            'body' => $data['body'],
        ]);

        $conversation->update(['last_message_at' => now(), 'status' => 'open']);

        return redirect()->route('chat.index');
    }
}
