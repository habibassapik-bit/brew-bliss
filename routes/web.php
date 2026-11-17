<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\OperationalCostController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/faq', [HomeController::class, 'faq'])->name('faq');

Route::get('/produk', [CatalogController::class, 'index'])->name('products.index');

Route::get('/produk/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('auth.register.store');

    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login.store');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/kategori/baru', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/kategori/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/produk', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/produk/baru', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/produk', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/produk/{product}', [AdminProductController::class, 'show'])->name('products.show');
        Route::get('/produk/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/produk/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/produk/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/pesanan/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/pesanan/{order}/batal', [AdminOrderController::class, 'cancel'])->name('orders.cancel');

        Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::post('/pembayaran/{payment}/setuju', [AdminPaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/pembayaran/{payment}/tolak', [AdminPaymentController::class, 'reject'])->name('payments.reject');

        Route::get('/pelanggan', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/pelanggan/{user}', [CustomerController::class, 'show'])->name('customers.show');

        Route::get('/kritik-saran', [AdminFeedbackController::class, 'index'])->name('feedbacks.index');
        Route::get('/kritik-saran/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedbacks.show');
        Route::post('/kritik-saran/{feedback}/status', [AdminFeedbackController::class, 'updateStatus'])->name('feedbacks.status');
        Route::post('/kritik-saran/{feedback}/balas', [AdminFeedbackController::class, 'reply'])->name('feedbacks.reply');

        Route::get('/customer-service', [ConversationController::class, 'index'])->name('conversations.index');
        Route::get('/customer-service/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::post('/customer-service/{conversation}/balas', [ConversationController::class, 'reply'])->name('conversations.reply');
        Route::post('/customer-service/{conversation}/status', [ConversationController::class, 'toggleStatus'])->name('conversations.status');

        Route::get('/voucher', [AdminVoucherController::class, 'index'])->name('vouchers.index');
        Route::get('/voucher/baru', [AdminVoucherController::class, 'create'])->name('vouchers.create');
        Route::post('/voucher', [AdminVoucherController::class, 'store'])->name('vouchers.store');
        Route::get('/voucher/{voucher}/edit', [AdminVoucherController::class, 'edit'])->name('vouchers.edit');
        Route::put('/voucher/{voucher}', [AdminVoucherController::class, 'update'])->name('vouchers.update');
        Route::delete('/voucher/{voucher}', [AdminVoucherController::class, 'destroy'])->name('vouchers.destroy');

        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('/biaya-operasional', [OperationalCostController::class, 'index'])->name('costs.index');
        Route::get('/biaya-operasional/baru', [OperationalCostController::class, 'create'])->name('costs.create');
        Route::post('/biaya-operasional', [OperationalCostController::class, 'store'])->name('costs.store');
        Route::get('/biaya-operasional/{cost}/edit', [OperationalCostController::class, 'edit'])->name('costs.edit');
        Route::put('/biaya-operasional/{cost}', [OperationalCostController::class, 'update'])->name('costs.update');
        Route::delete('/biaya-operasional/{cost}', [OperationalCostController::class, 'destroy'])->name('costs.destroy');

        Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
    });

Route::middleware('auth')->group(function (): void {
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/keranjang/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/keranjang/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/wishlist/{wishlist}/keranjang', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/sukses/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::post('/pesanan/{order}/pembayaran', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/pesanan/{order}/bukti-pembayaran', [PaymentController::class, 'proof'])->name('payments.proof');

    Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::post('/profil/alamat', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::patch('/profil/alamat/{address}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profil/alamat/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
    Route::post('/profil/alamat/{address}/utama', [ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default');

    Route::post('/produk/{product:slug}/ulasan', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/kritik-saran', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/kritik-saran', [FeedbackController::class, 'store'])->name('feedback.store');

    Route::get('/bantuan', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/bantuan/{conversation}', [ChatController::class, 'store'])->name('chat.store');
});
