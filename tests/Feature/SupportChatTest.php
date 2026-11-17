<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_chat(): void
    {
        $this->get('/bantuan')->assertRedirect('/masuk');
    }

    public function test_customer_can_open_chat_and_send_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bantuan');

        $response->assertOk();
        $response->assertSee('Belum ada pesan');

        $conversation = Conversation::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($conversation, 'Percakapan otomatis dibuat saat membuka bantuan.');

        $response = $this->actingAs($user)->post(route('chat.store', $conversation), [
            'body' => 'Halo, produk saya belum sampai?',
        ]);

        $response->assertRedirect('/bantuan');
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_role' => 'customer',
            'body' => 'Halo, produk saya belum sampai?',
        ]);
        $conversation->refresh();
        $this->assertSame('open', $conversation->status);
    }

    public function test_customer_cannot_read_or_send_to_other_conversation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $otherConversation = $other->conversations()->create(['status' => 'open', 'last_message_at' => now()]);
        $otherConversation->messages()->create([
            'sender_id' => $other->id,
            'sender_role' => 'customer',
            'body' => 'Pesan rahasia milik customer lain',
        ]);

        $response = $this->actingAs($user)->get('/bantuan');

        $response->assertOk();
        $response->assertDontSee('Pesan rahasia milik customer lain');

        $response = $this->actingAs($user)->post(route('chat.store', $otherConversation), [
            'body' => 'Coba kirim ke percakapan orang lain',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_invalid_message_is_rejected(): void
    {
        $user = User::factory()->create();
        $conversation = $user->conversations()->create(['status' => 'open', 'last_message_at' => now()]);

        $response = $this->actingAs($user)->post(route('chat.store', $conversation), [
            'body' => '',
        ]);

        $response->assertSessionHasErrors('body');
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_message_is_limited_to_2000_characters(): void
    {
        $user = User::factory()->create();
        $conversation = $user->conversations()->create(['status' => 'open', 'last_message_at' => now()]);

        $response = $this->actingAs($user)->post(route('chat.store', $conversation), [
            'body' => str_repeat('a', 2001),
        ]);

        $response->assertSessionHasErrors('body');
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_customer_cannot_access_admin_conversations(): void
    {
        $customer = User::factory()->create();
        $conversation = $customer->conversations()->create(['status' => 'open', 'last_message_at' => now()]);

        $this->actingAs($customer)->get(route('admin.conversations.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.conversations.show', $conversation))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.conversations.reply', $conversation), ['body' => 'pesan'])->assertForbidden();
    }

    public function test_super_admin_can_view_and_reply_conversations(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $customer = User::factory()->create(['name' => 'Budi Pelanggan']);
        $conversation = $customer->conversations()->create(['status' => 'open', 'last_message_at' => now()]);
        $conversation->messages()->create([
            'sender_id' => $customer->id,
            'sender_role' => 'customer',
            'body' => 'Pesanan saya kapan dikirim ya?',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.conversations.index'));

        $response->assertOk();
        $response->assertSee('Budi Pelanggan');

        $response = $this->actingAs($superAdmin)->get(route('admin.conversations.show', $conversation));

        $response->assertOk();
        $response->assertSee('Pesanan saya kapan dikirim ya?');

        $response = $this->actingAs($superAdmin)->post(route('admin.conversations.reply', $conversation), [
            'body' => 'Pesanan sedang kami proses hari ini.',
        ]);

        $response->assertRedirect(route('admin.conversations.show', $conversation));
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_role' => 'admin',
            'body' => 'Pesanan sedang kami proses hari ini.',
        ]);
        $conversation->refresh();
        $this->assertSame($superAdmin->id, $conversation->handled_by);
    }

    public function test_opened_conversations_mark_customer_messages_read(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $customer = User::factory()->create();
        $conversation = $customer->conversations()->create(['status' => 'open', 'last_message_at' => now()]);
        $message = $conversation->messages()->create([
            'sender_id' => $customer->id,
            'sender_role' => 'customer',
            'body' => 'Halo admin',
        ]);

        $this->actingAs($superAdmin)->get(route('admin.conversations.show', $conversation));

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_closed_conversation_rejects_customer_message(): void
    {
        $user = User::factory()->create();
        $conversation = $user->conversations()->create(['status' => 'closed', 'last_message_at' => now()]);

        $response = $this->actingAs($user)->post(route('chat.store', $conversation), [
            'body' => 'Coba kirim ke percakapan tertutup',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('messages', 0);
    }
}
