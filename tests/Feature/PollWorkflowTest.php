<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_poll_can_be_created_with_multiple_dates(): void
    {
        $user = User::factory()->create([
            'name' => 'Nora',
        ]);

        $response = $this->actingAs($user)->post(route('polls.store'), [
            'title' => 'Réunion équipe produit',
            'description' => 'Choisir la meilleure date pour le point mensuel.',
            'allows_multiple_choices' => '1',
            'dates' => [
                now()->addDays(2)->toDateString(),
                now()->addDays(4)->toDateString(),
                now()->addDays(6)->toDateString(),
            ],
        ]);

        $poll = Poll::query()->first();

        $response->assertRedirect(route('polls.show', $poll));
        $this->assertDatabaseHas('polls', [
            'title' => 'Réunion équipe produit',
            'organizer_name' => 'Nora',
            'user_id' => $user->id,
            'allows_multiple_choices' => true,
        ]);
        $this->assertDatabaseCount('poll_dates', 3);
    }

    public function test_a_guest_is_redirected_to_login_before_creating_a_poll(): void
    {
        $response = $this->post(route('polls.store'), [
            'title' => 'Sondage privé',
            'dates' => [
                now()->addDays(2)->toDateString(),
                now()->addDays(4)->toDateString(),
            ],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_can_be_filtered_by_range(): void
    {
        $user = User::factory()->create();

        $recentPoll = $user->polls()->create([
            'title' => 'Sondage récent',
            'organizer_name' => $user->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-recent-abcde',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $olderPoll = $user->polls()->create([
            'title' => 'Sondage ancien',
            'organizer_name' => $user->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-ancien-abcde',
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        $recentPoll->dates()->createMany([
            ['date' => now()->addDays(1)->toDateString()],
            ['date' => now()->addDays(3)->toDateString()],
        ]);

        $olderPoll->dates()->createMany([
            ['date' => now()->addDays(5)->toDateString()],
            ['date' => now()->addDays(7)->toDateString()],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['range' => '7d']));

        $response->assertOk();
        $response->assertSee('Sondage récent');
        $response->assertDontSee('Sondage ancien');
    }

    public function test_dashboard_can_be_exported_as_pdf(): void
    {
        $user = User::factory()->create();

        $poll = $user->polls()->create([
            'title' => 'Sondage PDF',
            'organizer_name' => $user->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-pdf-abcde',
        ]);

        $poll->dates()->createMany([
            ['date' => now()->addDays(2)->toDateString()],
            ['date' => now()->addDays(4)->toDateString()],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.export.pdf', ['range' => '30d']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_dashboard_can_be_exported_as_csv(): void
    {
        $user = User::factory()->create();

        $poll = $user->polls()->create([
            'title' => 'Sondage CSV',
            'organizer_name' => $user->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-csv-abcde',
        ]);

        $poll->dates()->createMany([
            ['date' => now()->addDays(2)->toDateString()],
            ['date' => now()->addDays(4)->toDateString()],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.export.csv', ['range' => '30d']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertSee('Sondage CSV');
    }

    public function test_dashboard_displays_popular_dates_chart_data(): void
    {
        $user = User::factory()->create();

        $poll = $user->polls()->create([
            'title' => 'Sondage analytics',
            'organizer_name' => $user->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-analytics-abcde',
        ]);

        $dates = $poll->dates()->createMany([
            ['date' => now()->addDays(2)->toDateString()],
            ['date' => now()->addDays(5)->toDateString()],
        ]);

        $response = $poll->responses()->create([
            'participant_name' => 'Lina',
            'comment' => null,
        ]);

        $response->choices()->create(['poll_date_id' => $dates[0]->id]);

        $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Créneaux les plus choisis');
        $dashboardResponse->assertSee('data-date-popularity-chart', false);
    }

    public function test_the_owner_does_not_see_the_participation_section_on_poll_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Nora',
        ]);

        $poll = $user->polls()->create([
            'title' => 'Sondage propriétaire',
            'organizer_name' => $user->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-proprietaire-abcde',
        ]);

        $poll->dates()->createMany([
            ['date' => now()->addDays(2)->toDateString()],
            ['date' => now()->addDays(4)->toDateString()],
        ]);

        $response = $this->actingAs($user)->get(route('polls.show', $poll));

        $response->assertOk();
        $response->assertDontSee('Indiquer ses disponibilités');
        $response->assertDontSee('Envoyer ma réponse');
        $response->assertSee('Gestion du sondage');
        $response->assertSee('Modifier le sondage');
        $response->assertSee('Participants par choix');
    }

    public function test_poll_participants_are_paginated_by_ten_for_the_owner(): void
    {
        $user = User::factory()->create([
            'name' => 'Nora',
        ]);

        $poll = $user->polls()->create([
            'title' => 'Sondage participants paginés',
            'organizer_name' => $user->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-participants-pagines-abcde',
        ]);

        $date = $poll->dates()->create([
            'date' => now()->addDays(2)->toDateString(),
        ]);

        foreach (range(1, 12) as $index) {
            $response = $poll->responses()->create([
                'participant_name' => 'Participant '.$index,
            ]);

            $response->choices()->create([
                'poll_date_id' => $date->id,
            ]);
        }

        $firstPage = $this->actingAs($user)->get(route('polls.show', $poll));

        $firstPage->assertOk();
        $firstPage->assertViewHas('responses', fn ($responses) => $responses->count() === 10 && $responses->total() === 12);

        $secondPage = $this->actingAs($user)->get(route('polls.show', [$poll, 'page' => 2]));

        $secondPage->assertOk();
        $secondPage->assertViewHas('responses', fn ($responses) => $responses->count() === 2 && $responses->currentPage() === 2);
    }

    public function test_the_owner_can_update_a_poll_from_the_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Nora',
        ]);

        $poll = $user->polls()->create([
            'title' => 'Réunion équipe produit',
            'organizer_name' => 'Nora',
            'slug' => 'reunion-equipe-produit-abcde',
            'description' => 'Version initiale',
        ]);

        $keptDate = $poll->dates()->create(['date' => now()->addDays(2)->toDateString()]);
        $removedDate = $poll->dates()->create(['date' => now()->addDays(4)->toDateString()]);

        $response = $this->actingAs($user)->patch(route('polls.update', $poll), [
            'title' => 'Réunion équipe produit - mise à jour',
            'description' => 'Version finale',
            'allows_multiple_choices' => '0',
            'dates' => [
                $keptDate->date->toDateString(),
                now()->addDays(6)->toDateString(),
            ],
        ]);

        $response->assertRedirect(route('polls.show', $poll));
        $this->assertDatabaseHas('polls', [
            'id' => $poll->id,
            'title' => 'Réunion équipe produit - mise à jour',
            'description' => 'Version finale',
            'allows_multiple_choices' => false,
        ]);
        $this->assertDatabaseMissing('poll_dates', [
            'id' => $removedDate->id,
        ]);
        $this->assertTrue(
            $poll->fresh()->dates()->whereDate('date', now()->addDays(6)->toDateString())->exists()
        );
    }

    public function test_a_user_cannot_update_someone_elses_poll(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $poll = $owner->polls()->create([
            'title' => 'Sondage privé',
            'organizer_name' => $owner->name,
            'slug' => 'sondage-prive-abcde',
        ]);

        $poll->dates()->createMany([
            ['date' => now()->addDays(2)->toDateString()],
            ['date' => now()->addDays(5)->toDateString()],
        ]);

        $response = $this->actingAs($intruder)->patch(route('polls.update', $poll), [
            'title' => 'Tentative',
            'allows_multiple_choices' => '1',
            'dates' => [
                now()->addDays(2)->toDateString(),
                now()->addDays(6)->toDateString(),
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_the_owner_can_delete_a_poll(): void
    {
        $user = User::factory()->create();

        $poll = $user->polls()->create([
            'title' => 'Sondage à supprimer',
            'organizer_name' => $user->name,
            'slug' => 'sondage-a-supprimer-abcde',
        ]);

        $poll->dates()->createMany([
            ['date' => now()->addDays(2)->toDateString()],
            ['date' => now()->addDays(4)->toDateString()],
        ]);

        $response = $this->actingAs($user)->delete(route('polls.destroy', $poll));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('polls', [
            'id' => $poll->id,
        ]);
    }

    public function test_a_participant_can_submit_availability_for_a_poll(): void
    {
        $poll = Poll::query()->create([
            'title' => 'Déjeuner équipe',
            'organizer_name' => 'Sami',
            'allows_multiple_choices' => true,
            'slug' => 'dejeuner-equipe-abcde',
        ]);

        $dates = $poll->dates()->createMany([
            ['date' => now()->addDays(3)->toDateString()],
            ['date' => now()->addDays(5)->toDateString()],
        ]);

        $response = $this->post(route('polls.responses.store', $poll), [
            'participant_name' => 'Lina',
            'comment' => 'Disponible surtout en fin de journée.',
            'dates' => [$dates[0]->id, $dates[1]->id],
        ]);

        $response->assertRedirect(route('polls.show', $poll).'#reponses');
        $this->assertDatabaseHas('poll_responses', [
            'poll_id' => $poll->id,
            'participant_name' => 'Lina',
        ]);
        $this->assertDatabaseCount('poll_response_choices', 2);
    }

    public function test_a_participant_can_submit_only_one_date_when_the_poll_requires_it(): void
    {
        $poll = Poll::query()->create([
            'title' => 'Choix unique',
            'organizer_name' => 'Sami',
            'allows_multiple_choices' => false,
            'slug' => 'choix-unique-abcde',
        ]);

        $dates = $poll->dates()->createMany([
            ['date' => now()->addDays(3)->toDateString()],
            ['date' => now()->addDays(5)->toDateString()],
        ]);

        $response = $this->post(route('polls.responses.store', $poll), [
            'participant_name' => 'Lina',
            'selected_date' => $dates[0]->id,
        ]);

        $response->assertRedirect(route('polls.show', $poll).'#reponses');
        $this->assertDatabaseCount('poll_response_choices', 1);
        $this->assertDatabaseHas('poll_response_choices', [
            'poll_date_id' => $dates[0]->id,
        ]);
    }
}