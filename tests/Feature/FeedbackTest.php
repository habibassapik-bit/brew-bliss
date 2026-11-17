<?php

namespace Tests\Feature;

use App\Enums\FeedbackStatus;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_feedback_page(): void
    {
        $this->get('/kritik-saran')->assertRedirect('/masuk');
        $this->post('/kritik-saran', [
            'category' => 'service',
            'subject' => 'Pelayanan',
            'message' => 'Masukan dari guest',
        ])->assertRedirect('/masuk');

        $this->assertDatabaseCount('feedbacks', 0);
    }

    public function test_customer_can_send_feedback(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/kritik-saran', [
            'category' => 'taste',
            'subject' => 'Rasa donat',
            'message' => 'Donatnya enak banget, semakin bertambah!',
        ]);

        $response->assertRedirect('/kritik-saran');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'category' => 'taste',
            'subject' => 'Rasa donat',
            'status' => 'new',
        ]);
    }

    public function test_invalid_feedback_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/kritik-saran', [
            'category' => 'kategori_ngawur',
            'subject' => '',
            'message' => 'pendek',
        ]);

        $response->assertSessionHasErrors(['category', 'subject', 'message']);
        $this->assertDatabaseCount('feedbacks', 0);
    }

    public function test_customer_sees_own_feedback_history(): void
    {
        $user = User::factory()->create();
        Feedback::query()->create([
            'user_id' => $user->id,
            'category' => 'price',
            'subject' => 'Harga ongkir',
            'message' => 'Ongkirnya bisa dikurangi tidak ya?',
        ]);

        $response = $this->actingAs($user)->get('/kritik-saran');

        $response->assertOk();
        $response->assertSee('Harga ongkir');
    }

    public function test_customer_only_sees_own_feedback(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Feedback::query()->create([
            'user_id' => $other->id,
            'category' => 'service',
            'subject' => 'Masukan milik orang lain',
            'message' => 'Pesan yang hanya untuk milik pengirim lain.',
        ]);

        $response = $this->actingAs($user)->get('/kritik-saran');

        $response->assertOk();
        $response->assertDontSee('Masukan milik orang lain');
    }

    public function test_customer_cannot_access_admin_feedback_pages(): void
    {
        $customer = User::factory()->create();
        $feedback = Feedback::query()->create([
            'user_id' => $customer->id,
            'category' => 'service',
            'subject' => 'Test',
            'message' => 'Isi masukan untuk pengujian.',
        ]);

        $this->actingAs($customer)->get(route('admin.feedbacks.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.feedbacks.show', $feedback))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.feedbacks.status', $feedback), ['status' => 'done'])->assertForbidden();
    }

    public function test_super_admin_can_view_and_manage_feedback(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        $customer = User::factory()->create();

        $feedback = Feedback::query()->create([
            'user_id' => $customer->id,
            'category' => 'website',
            'subject' => 'Tombol checkout',
            'message' => 'Tombol checkout kadang tidak responsif di mobile.',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.feedbacks.index'));

        $response->assertOk();
        $response->assertSee('Tombol checkout');

        $response = $this->actingAs($superAdmin)->get(route('admin.feedbacks.show', $feedback));

        $response->assertOk();
        $feedback->refresh();
        $this->assertSame(FeedbackStatus::Read, $feedback->status, 'Masukan baru otomatis ditandai dibaca saat dibuka admin.');

        $response = $this->actingAs($superAdmin)->post(route('admin.feedbacks.reply', $feedback), [
            'admin_reply' => 'Terima kasih masukannya, sedang kami perbaiki.',
        ]);

        $response->assertRedirect(route('admin.feedbacks.show', $feedback));
        $this->assertDatabaseHas('feedbacks', [
            'id' => $feedback->id,
            'admin_reply' => 'Terima kasih masukannya, sedang kami perbaiki.',
            'replied_by' => $superAdmin->id,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.feedbacks.status', $feedback), [
            'status' => 'done',
        ]);

        $response->assertRedirect(route('admin.feedbacks.show', $feedback));
        $this->assertDatabaseHas('feedbacks', ['id' => $feedback->id, 'status' => 'done']);
    }

    public function test_reply_requires_minimum_length(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        $customer = User::factory()->create();

        $feedback = Feedback::query()->create([
            'user_id' => $customer->id,
            'category' => 'other',
            'subject' => 'Masukan tes',
            'message' => 'Isi masukan untuk pengujian balasan.',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.feedbacks.reply', $feedback), [
            'admin_reply' => 'ok',
        ]);

        $response->assertSessionHasErrors('admin_reply');
    }
}
