<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeedbackStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Tampilkan daftar kritik dan saran dengan filter status.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->string('status');

        $feedbacks = Feedback::query()
            ->with('user')
            ->when(in_array($status, FeedbackStatus::values(), true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = Feedback::query()->where('status', FeedbackStatus::New->value)->count();

        return view('admin.feedbacks.index', [
            'feedbacks' => $feedbacks,
            'statuses' => FeedbackStatus::cases(),
            'statusFilter' => $status,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Tampilkan detail kritik dan saran (menandai baru menjadi dibaca).
     */
    public function show(Feedback $feedback): View
    {
        if ($feedback->status === FeedbackStatus::New) {
            $feedback->update(['status' => FeedbackStatus::Read]);
        }

        return view('admin.feedbacks.show', [
            'feedback' => $feedback->load(['user', 'repliedBy']),
        ]);
    }

    /**
     * Ubah status kritik dan saran.
     */
    public function updateStatus(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', FeedbackStatus::values())],
        ]);

        $status = FeedbackStatus::from($data['status']);
        $feedback->update(['status' => $status]);

        return redirect()
            ->route('admin.feedbacks.show', $feedback)
            ->with('status', 'Status masukan diperbarui menjadi '.$status->label().'.');
    }

    /**
     * Balas kritik dan saran dari customer.
     */
    public function reply(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $feedback->update([
            'admin_reply' => $data['admin_reply'],
            'replied_by' => auth()->id(),
            'replied_at' => now(),
        ]);

        return redirect()
            ->route('admin.feedbacks.show', $feedback)
            ->with('status', 'Balasan berhasil dikirim.');
    }
}
