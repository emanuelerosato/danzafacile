<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Feature: Admin Public Events Management
 *
 * Valida le funzionalità amministrative per la gestione degli eventi pubblici,
 * inclusi dashboard, report, export e personalizzazione landing pages.
 */
class AdminPublicEventsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create([
            'name' => 'Test Dance School',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'school_id' => $this->school->id,
            'email' => 'admin@test.com',
        ]);
    }

    /** @test */
    public function admin_can_access_public_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.events.public-dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.events.public-dashboard');
        $response->assertViewHas('stats');
    }

    /** @test */
    public function admin_can_view_guest_report()
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
        ]);

        $guest = User::factory()->create(['is_guest' => true]);
        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $guest->id,
        ]);

        $response = $this->get(route('admin.events.guest-report'));

        $response->assertOk();
        $response->assertViewIs('admin.events.guest-report');
        $response->assertSee($guest->email);
    }

    /** @test */
    public function admin_can_export_guest_registrations_csv()
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
            'title' => 'Test Event',
        ]);

        $guest = User::factory()->create([
            'is_guest' => true,
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
        ]);

        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $guest->id,
            'status' => 'confirmed',
        ]);

        $response = $this->get(route('admin.events.export-guests'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertDownload();

        // Verifica contenuto CSV
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Test Guest', $csv);
        $this->assertStringContainsString('guest@test.com', $csv);
    }

    /** @test */
    public function admin_can_customize_event_landing()
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
        ]);

        $response = $this->get(route('admin.events.customize-landing', $event));

        $response->assertOk();
        $response->assertViewIs('admin.events.customize-landing');
        $response->assertViewHas('event');
    }

    /** @test */
    public function admin_can_update_event_landing_customization()
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
            'landing_config' => [],
        ]);

        $customization = [
            'hero_title' => 'Custom Hero Title',
            'hero_subtitle' => 'Custom Subtitle',
            'primary_color' => '#FF5733',
            'show_location' => true,
        ];

        $response = $this->patch(
            route('admin.events.update-landing', $event),
            ['landing_config' => $customization]
        );

        $response->assertRedirect();

        // Verifica configurazione salvata
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
        ]);

        $updatedEvent = $event->fresh();
        $this->assertEquals('Custom Hero Title', $updatedEvent->landing_config['hero_title'] ?? null);
    }

    /** @test */
    public function admin_sees_only_their_school_events()
    {
        $this->actingAs($this->admin);

        // Eventi della propria scuola
        $ownEvent = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
            'title' => 'Own School Event',
        ]);

        // Eventi di altra scuola
        $otherSchool = School::factory()->create();
        $otherEvent = Event::factory()->create([
            'school_id' => $otherSchool->id,
            'is_public' => true,
            'title' => 'Other School Event',
        ]);

        $response = $this->get(route('admin.events.index'));

        $response->assertOk();
        $response->assertSee('Own School Event');
        $response->assertDontSee('Other School Event');
    }

    /** @test */
    public function admin_can_view_event_registrations()
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
        ]);

        $guest1 = User::factory()->create(['is_guest' => true, 'name' => 'Guest One']);
        $guest2 = User::factory()->create(['is_guest' => true, 'name' => 'Guest Two']);

        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $guest1->id,
            'status' => 'confirmed',
        ]);

        EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $guest2->id,
            'status' => 'pending_payment',
        ]);

        $response = $this->get(route('admin.events.registrations', $event));

        $response->assertOk();
        $response->assertSee('Guest One');
        $response->assertSee('Guest Two');
        $response->assertSee('confirmed');
        $response->assertSee('pending_payment');
    }

    /** @test */
    public function admin_can_cancel_registration()
    {
        $this->actingAs($this->admin);

        $event = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
        ]);

        $guest = User::factory()->create(['is_guest' => true]);

        $registration = EventRegistration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $guest->id,
            'status' => 'confirmed',
        ]);

        $response = $this->delete(route('admin.events.cancel-registration', $registration));

        $response->assertRedirect();

        // Verifica stato aggiornato
        $this->assertDatabaseHas('event_registrations', [
            'id' => $registration->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function admin_cannot_access_other_school_events()
    {
        $this->actingAs($this->admin);

        $otherSchool = School::factory()->create();
        $otherEvent = Event::factory()->create([
            'school_id' => $otherSchool->id,
            'is_public' => true,
        ]);

        $response = $this->get(route('admin.events.edit', $otherEvent));

        $response->assertForbidden();
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard()
    {
        $guest = User::factory()->create([
            'role' => 'user',
            'is_guest' => true,
        ]);

        $this->actingAs($guest);

        $response = $this->get(route('admin.events.public-dashboard'));

        $response->assertForbidden();
    }

    /** @test */
    public function public_dashboard_shows_correct_stats()
    {
        $this->actingAs($this->admin);

        // Crea eventi con vari stati
        $publicEvent = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true,
        ]);

        $privateEvent = Event::factory()->create([
            'school_id' => $this->school->id,
            'is_public' => false,
        ]);

        // Crea registrazioni
        $guest1 = User::factory()->create(['is_guest' => true]);
        $guest2 = User::factory()->create(['is_guest' => true]);

        EventRegistration::factory()->create([
            'event_id' => $publicEvent->id,
            'user_id' => $guest1->id,
            'status' => 'confirmed',
        ]);

        EventRegistration::factory()->create([
            'event_id' => $publicEvent->id,
            'user_id' => $guest2->id,
            'status' => 'pending_payment',
        ]);

        $response = $this->get(route('admin.events.public-dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_public_events'] >= 1
                && $stats['total_registrations'] >= 2;
        });
    }
}
