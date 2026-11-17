<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    /**
     * Tampilkan daftar percakapan customer.
     */
    public function index(): View
    {
        $conversations = Conversation::query()
            ->with(['user', 'messages'])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('admin.conversations.index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Buka percakapan customer dan tandai pesan sebagai dibaca.
     */
    public function show(Conversation $conversation): View
    {
        $conversation->messages()->where('sender_role', 'customer')->whereNull('read_at')->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

        return view('admin.conversations.show', [
            'conversation' => $conversation->load('user'),
            'messages' => $messages,
        ]);
    }

    /**
     * Balas percakapan customer sebagai Super Admin.
     */
    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'sender_role' => 'admin',
            'body' => $data['body'],
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'handled_by' => auth()->id(),
            'status' => 'open',
        ]);

        return redirect()->route('admin.conversations.show', $conversation);
    }

    /**
     * Tutup atau buka kembali percakapan.
     */
    public function toggleStatus(Conversation $conversation): RedirectResponse
    {
        $newStatus = $conversation->status === 'open' ? 'closed' : 'open';

        $conversation->update(['status' => $newStatus]);

        return redirect()
            ->route('admin.conversations.show', $conversation)
            ->with('status', $newStatus === 'closed' ? 'Percakapan ditutup.' : 'Percakapan dibuka kembali.');
    }
}
