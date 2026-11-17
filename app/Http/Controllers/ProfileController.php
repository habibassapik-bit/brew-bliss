<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil beserta alamat tersimpan.
     */
    public function index(): View
    {
        $user = auth()->user();

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('profile.index', [
            'user' => $user,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Perbarui data profil milik customer yang sedang masuk.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
        ]);

        $user->update($data);

        return redirect()
            ->route('profile.index')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Ubah kata sandi dengan verifikasi kata sandi lama.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);
        }

        $user->update(['password' => $data['password']]);

        return redirect()
            ->route('profile.index')
            ->with('status', 'Kata sandi berhasil diubah.');
    }

    /**
     * Simpan alamat baru milik customer.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $this->validateAddress($request);

        $address = auth()->user()->addresses()->create([
            ...$data,
            'is_default' => $this->shouldBecomeDefault($request),
        ]);

        if ($address->is_default) {
            $this->ensureSingleDefault($address);
        }

        return redirect()
            ->route('profile.index')
            ->with('status', 'Alamat berhasil ditambahkan.');
    }

    /**
     * Perbarui alamat milik customer.
     */
    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        abort_if($address->user_id !== auth()->id(), 404);

        $data = $this->validateAddress($request);

        $address->update([
            ...$data,
            'is_default' => $this->shouldBecomeDefault($request),
        ]);

        if ($address->is_default) {
            $this->ensureSingleDefault($address);
        }

        return redirect()
            ->route('profile.index')
            ->with('status', 'Alamat berhasil diperbarui.');
    }

    /**
     * Hapus alamat milik customer.
     */
    public function destroyAddress(Address $address): RedirectResponse
    {
        abort_if($address->user_id !== auth()->id(), 404);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $fallback = auth()->user()->addresses()->orderByDesc('created_at')->first();

            if ($fallback !== null) {
                $fallback->update(['is_default' => true]);
            }
        }

        return redirect()
            ->route('profile.index')
            ->with('status', 'Alamat berhasil dihapus.');
    }

    /**
     * Jadikan alamat sebagai alamat utama customer.
     */
    public function setDefaultAddress(Address $address): RedirectResponse
    {
        abort_if($address->user_id !== auth()->id(), 404);

        $address->update(['is_default' => true]);
        $this->ensureSingleDefault($address);

        return redirect()
            ->route('profile.index')
            ->with('status', 'Alamat utama diperbarui.');
    }

    /**
     * Validasi input alamat.
     *
     * @return array<string, mixed>
     */
    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'address' => ['required', 'string', 'max:500'],
            'label' => ['nullable', 'string', 'max:50'],
        ]);
    }

    /**
     * Tentukan apakah alamat yang disimpan harus menjadi alamat utama.
     */
    private function shouldBecomeDefault(Request $request): bool
    {
        if ($request->boolean('is_default')) {
            return true;
        }

        return auth()->user()->addresses()->where('is_default', true)->doesntExist();
    }

    /**
     * Pastikan hanya satu alamat utama per customer.
     */
    private function ensureSingleDefault(Address $address): void
    {
        auth()->user()->addresses()
            ->where('is_default', true)
            ->whereKeyNot($address->id)
            ->update(['is_default' => false]);
    }
}
