<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Tampilkan daftar customer (pengguna tanpa role admin).
     */
    public function index(Request $request): View
    {
        $search = (string) $request->string('q');

        $customers = $this->customerQuery()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->withCount('orders')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    /**
     * Tampilkan detail customer beserta ringkasan transaksinya.
     */
    public function show(User $user): View
    {
        abort_if($user->hasRole('super_admin') || $user->hasRole('admin_cs'), 404);

        $orders = $user->orders()
            ->latest()
            ->paginate(10);

        $totalSpent = $user->orders()
            ->whereIn('payment_status', ['paid'])
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        return view('admin.customers.show', [
            'customer' => $user,
            'orders' => $orders,
            'totalSpent' => (float) $totalSpent,
        ]);
    }

    /**
     * Query dasar customer: pengguna tanpa role admin.
     */
    private function customerQuery(): Builder
    {
        return User::query()
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['super_admin', 'admin_cs']));
    }
}
