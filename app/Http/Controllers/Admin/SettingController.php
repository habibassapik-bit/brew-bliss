<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Kunci pengaturan toko yang dapat diedit Super Admin.
     *
     * @var list<string>
     */
    private const EDITABLE_KEYS = [
        'store_name',
        'store_description',
        'store_address',
        'store_phone',
        'store_open_hours',
        'store_whatsapp',
    ];

    /**
     * Tampilkan form pengaturan toko.
     */
    public function edit(): View
    {
        $values = collect(self::EDITABLE_KEYS)
            ->mapWithKeys(fn (string $key): array => [$key => (string) Setting::get($key, '')]);

        return view('admin.settings.edit', [
            'values' => $values,
        ]);
    }

    /**
     * Simpan pengaturan toko.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:1000'],
            'store_address' => ['required', 'string', 'max:255'],
            'store_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'store_open_hours' => ['required', 'string', 'max:100'],
            'store_whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
        ], [
            'store_phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'store_whatsapp.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
        ]);

        $data['store_whatsapp'] = $this->normalizeWhatsappNumber($data['store_whatsapp'] ?? '');

        foreach (self::EDITABLE_KEYS as $key) {
            Setting::set($key, $data[$key] ?? '');
        }

        ActivityLogger::log('settings.updated', null, [
            'description' => 'Memperbarui pengaturan informasi toko.',
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan toko berhasil diperbarui.');
    }

    /**
     * Samakan format nomor WhatsApp ke format internasional (62...) untuk link wa.me.
     */
    private function normalizeWhatsappNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number) ?? '';

        if ($number === '') {
            return '';
        }

        if (str_starts_with($number, '0')) {
            return '62'.substr($number, 1);
        }

        if (! str_starts_with($number, '62')) {
            return '62'.$number;
        }

        return $number;
    }
}
