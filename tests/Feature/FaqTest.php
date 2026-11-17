<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_faq_page(): void
    {
        $response = $this->get(route('faq'));

        $response->assertOk();
        $response->assertSee('Pertanyaan yang Sering Diajukan');
    }

    public function test_customer_can_access_faq_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('faq'));

        $response->assertOk();
    }

    public function test_faq_page_contains_relevant_topics(): void
    {
        $response = $this->get(route('faq'));

        $response->assertOk();
        $response->assertSee('Bagaimana cara membuat pesanan?');
        $response->assertSee('Bagaimana cara menggunakan voucher?');
        $response->assertSee('Bagaimana cara melakukan pembayaran?');
        $response->assertSee('Bagaimana cara melihat status pesanan saya?');
        $response->assertSee('Berapa biaya pengiriman (ongkir)?');
        $response->assertSee('Rp3.000');
        $response->assertSee('Bagaimana cara menghubungi customer service?');
        $response->assertSee(route('chat.index'), false);
    }

    public function test_home_page_links_to_faq(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(route('faq'), false);
    }
}
