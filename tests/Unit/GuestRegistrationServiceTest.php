<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Models\EventRegistration;
use App\Services\GuestRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

/**
 * Test Unit: GuestRegistrationService
 *
 * Valida la logica di business per la gestione degli utenti guest,
 * inclusi creazione, riutilizzo e cleanup di account temporanei.
 */
class GuestRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GuestRegistrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GuestRegistrationService::class);
    }

    /** @test */
    public function it_creates_new_guest_user()
    {
        $event = Event::factory()->create(['is_public' => true]);

        $result = $this->service->registerGuest($event, [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '+39 123456789',
            'gdpr_consents' => [
                'privacy' => true,
                'marketing' => false,
            ],
        ]);

        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertTrue($result['user']->is_guest);
        $this->assertNotNull($result['user']->guest_token);
        $this->assertNotNull($result['magic_link']);

        // Verifica formato magic link
        $this->assertStringContainsString('/guest/magic-login/', $result['magic_link']);

        // Verifica dati salvati
        $this->assertDatabaseHas('users', [
            'email' => 'john@test.com',
            'name' => 'John Doe',
            'is_guest' => true,
        ]);

        // Verifica GDPR consents
        $user = $result['user'];
        $this->assertTrue($user->hasGdprConsent('privacy'));
        $this->assertFalse($user->hasGdprConsent('marketing'));
    }

    /** @test */
    public function it_reuses_existing_guest_user()
    {
        $existingGuest = User::factory()->create([
            'email' => 'existing@test.com',
            'name' => 'Old Name',
            'is_guest' => true,
            'guest_token' => Str::random(32),
        ]);

        $event = Event::factory()->create(['is_public' => true]);

        $result = $this->service->registerGuest($event, [
            'name' => 'Updated Name',
            'email' => 'existing@test.com',
            'phone' => '+39 999999999',
            'gdpr_consents' => ['privacy' => true],
        ]);

        // Verifica che lo stesso user venga riutilizzato
        $this->assertEquals($existingGuest->id, $result['user']->id);

        // Verifica aggiornamento dati
        $updatedUser = $result['user']->fresh();
        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('+39 999999999', $updatedUser->phone);

        // Verifica che il guest_token sia stato rigenerato
        $this->assertNotEquals($existingGuest->guest_token, $updatedUser->guest_token);

        // Verifica che non ci siano duplicati
        $this->assertEquals(1, User::where('email', 'existing@test.com')->count());
    }

    /** @test */
    public function it_cleans_up_expired_guests()
    {
        // Old guest (200 days ago) - should be archived
        $oldGuest = User::factory()->create([
            'email' => 'old@test.com',
            'is_guest' => true,
            'created_at' => now()->subDays(200),
        ]);

        // Recent guest (100 days ago) - should be kept
        $recentGuest = User::factory()->create([
            'email' => 'recent@test.com',
            'is_guest' => true,
            'created_at' => now()->subDays(100),
        ]);

        // Recent normal user (should not be touched)
        $normalUser = User::factory()->create([
            'email' => 'normal@test.com',
            'is_guest' => false,
            'created_at' => now()->subDays(300),
        ]);

        $count = $this->service->cleanupExpiredGuests(180);

        // Verifica che solo 1 guest sia stato archiviato
        $this->assertEquals(1, $count);

        // Verifica stato degli utenti
        $this->assertTrue($oldGuest->fresh()->is_archived);
        $this->assertFalse($recentGuest->fresh()->is_archived ?? false);
        $this->assertFalse($normalUser->fresh()->is_archived ?? false);
    }

    /** @test */
    public function it_generates_unique_guest_tokens()
    {
        $event = Event::factory()->create(['is_public' => true]);

        $result1 = $this->service->registerGuest($event, [
            'name' => 'Guest One',
            'email' => 'guest1@test.com',
            'phone' => '+39 111111111',
            'gdpr_consents' => ['privacy' => true],
        ]);

        $result2 = $this->service->registerGuest($event, [
            'name' => 'Guest Two',
            'email' => 'guest2@test.com',
            'phone' => '+39 222222222',
            'gdpr_consents' => ['privacy' => true],
        ]);

        // Verifica che i token siano diversi
        $this->assertNotEquals(
            $result1['user']->guest_token,
            $result2['user']->guest_token
        );

        // Verifica che i magic link siano diversi
        $this->assertNotEquals(
            $result1['magic_link'],
            $result2['magic_link']
        );
    }

    /** @test */
    public function it_handles_guest_with_existing_registrations()
    {
        $existingGuest = User::factory()->create([
            'email' => 'repeat@test.com',
            'is_guest' => true,
        ]);

        $oldEvent = Event::factory()->create(['is_public' => true]);
        EventRegistration::factory()->create([
            'user_id' => $existingGuest->id,
            'event_id' => $oldEvent->id,
            'status' => 'confirmed',
        ]);

        $newEvent = Event::factory()->create(['is_public' => true]);

        $result = $this->service->registerGuest($newEvent, [
            'name' => 'Repeat Guest',
            'email' => 'repeat@test.com',
            'phone' => '+39 333333333',
            'gdpr_consents' => ['privacy' => true],
        ]);

        // Verifica che lo stesso guest venga riutilizzato
        $this->assertEquals($existingGuest->id, $result['user']->id);

        // Verifica che ci siano 2 registrazioni totali
        $this->assertEquals(2, EventRegistration::where('user_id', $existingGuest->id)->count());
    }

    /** @test */
    public function cleanup_preserves_guests_with_active_registrations()
    {
        // Old guest con registrazione attiva
        $activeGuest = User::factory()->create([
            'email' => 'active@test.com',
            'is_guest' => true,
            'created_at' => now()->subDays(200),
        ]);

        $upcomingEvent = Event::factory()->create([
            'is_public' => true,
            'date' => now()->addDays(10),
        ]);

        EventRegistration::factory()->create([
            'user_id' => $activeGuest->id,
            'event_id' => $upcomingEvent->id,
            'status' => 'confirmed',
        ]);

        $count = $this->service->cleanupExpiredGuests(180);

        // Non dovrebbe archiviare guest con registrazioni future
        $this->assertEquals(0, $count);
        $this->assertFalse($activeGuest->fresh()->is_archived ?? false);
    }
}
