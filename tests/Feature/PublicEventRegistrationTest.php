<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Models\EventRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Critical Path: Registrazione Guest a Eventi Pubblici
 *
 * Valida il flusso principale di registrazione per utenti guest,
 * includendo validazione form, privacy consent e gestione pagamenti.
 */
class PublicEventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_register_to_public_event()
    {
        $event = Event::factory()->create([
            'is_public' => true,
            'active' => true,
            'price' => 0, // free event
        ]);

        $response = $this->post(route('public.events.register', $event->slug), [
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'phone' => '+39 123456789',
            'privacy_consent' => true,
            'g-recaptcha-response' => 'test-token',
        ]);

        $response->assertRedirect();

        // Verifica creazione user guest
        $this->assertDatabaseHas('users', [
            'email' => 'guest@test.com',
            'is_guest' => true,
        ]);

        // Verifica registrazione confermata (evento gratuito)
        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function registration_requires_privacy_consent()
    {
        $event = Event::factory()->create(['is_public' => true]);

        $response = $this->post(route('public.events.register', $event->slug), [
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'phone' => '+39 123456789',
            // privacy_consent missing
        ]);

        $response->assertSessionHasErrors('privacy_consent');

        // Verifica che nessun user sia stato creato
        $this->assertDatabaseMissing('users', [
            'email' => 'guest@test.com',
        ]);
    }

    /** @test */
    public function paid_event_creates_pending_payment()
    {
        $event = Event::factory()->create([
            'is_public' => true,
            'price' => 25.00,
        ]);

        $response = $this->post(route('public.events.register', $event->slug), [
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'phone' => '+39 123456789',
            'privacy_consent' => true,
            'g-recaptcha-response' => 'test-token',
        ]);

        // Verifica registrazione in attesa di pagamento
        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'status' => 'pending_payment',
        ]);

        // Verifica creazione record pagamento
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('status', 'pending_payment')
            ->first();

        $this->assertNotNull($registration);
        $this->assertDatabaseHas('event_payments', [
            'event_registration_id' => $registration->id,
            'amount' => 25.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function cannot_register_to_inactive_event()
    {
        $event = Event::factory()->create([
            'is_public' => true,
            'active' => false,
        ]);

        $response = $this->post(route('public.events.register', $event->slug), [
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'phone' => '+39 123456789',
            'privacy_consent' => true,
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function cannot_register_to_non_public_event()
    {
        $event = Event::factory()->create([
            'is_public' => false,
            'active' => true,
        ]);

        $response = $this->get(route('public.events.show', $event->slug));

        $response->assertStatus(404);
    }

    /** @test */
    public function email_validation_prevents_invalid_emails()
    {
        $event = Event::factory()->create(['is_public' => true]);

        $response = $this->post(route('public.events.register', $event->slug), [
            'name' => 'Test Guest',
            'email' => 'invalid-email',
            'phone' => '+39 123456789',
            'privacy_consent' => true,
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function duplicate_registration_is_prevented()
    {
        $event = Event::factory()->create([
            'is_public' => true,
            'price' => 0,
        ]);

        // Prima registrazione
        $this->post(route('public.events.register', $event->slug), [
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'phone' => '+39 123456789',
            'privacy_consent' => true,
            'g-recaptcha-response' => 'test-token',
        ]);

        // Tentativo di registrazione duplicata
        $response = $this->post(route('public.events.register', $event->slug), [
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'phone' => '+39 123456789',
            'privacy_consent' => true,
            'g-recaptcha-response' => 'test-token',
        ]);

        // Verifica che esista solo una registrazione
        $this->assertEquals(1, EventRegistration::where('event_id', $event->id)->count());
    }
}
