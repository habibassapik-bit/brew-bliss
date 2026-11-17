<?php

namespace App\Http\Controllers;

use App\Enums\FeedbackStatus;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Daftar kategori kritik dan saran beserta labelnya.
     *
     * @return array<string, string>
     */
    public const CATEGORIES = [
        'food_quality' => 'Kualitas Makanan',
        'taste' => 'Rasa Produk',
        'price' => 'Harga',
        'service' => 'Pelayanan',
        'delivery' => 'Pengiriman',
        'website' => 'Website',
        'other' => 'Lainnya',
    ];

    /**
     * Tampilkan formulir kritik & saran beserta riwayat milik customer.
     */
    public function index(): View
    {
        $feedbacks = auth()->user()->feedbacks()->latest()->get();

        return view('feedback.index', [
            'feedbacks' => $feedbacks,
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Simpan kritik & saran dari customer yang login.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(self::CATEGORIES))],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        Feedback::query()->create([
            ...$data,
            'user_id' => auth()->id(),
            'is_anonymous' => $request->boolean('is_anonymous'),
            'status' => FeedbackStatus::New,
        ]);

        return redirect()
            ->route('feedback.index')
            ->with('status', 'Kritik dan saran berhasil dikirim. Terima kasih atas masukannya!');
    }
}
