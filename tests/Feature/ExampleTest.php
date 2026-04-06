<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_homepage_hides_recent_polls_list(): void
    {
        Poll::query()->create([
            'title' => 'Sondage confidentiel',
            'organizer_name' => 'Nora',
            'allows_multiple_choices' => true,
            'slug' => 'sondage-confidentiel-abcde',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Sondage confidentiel');
        $response->assertDontSee('Voir un exemple public');
        $response->assertSee('Les sondages récents restent privés');
    }

    public function test_authenticated_creator_only_sees_their_own_recent_polls(): void
    {
        $owner = User::factory()->create([
            'name' => 'Nora',
        ]);

        $otherUser = User::factory()->create([
            'name' => 'Sami',
        ]);

        $owner->polls()->create([
            'title' => 'Mon sondage privé',
            'organizer_name' => $owner->name,
            'allows_multiple_choices' => true,
            'slug' => 'mon-sondage-prive-abcde',
        ]);

        $otherUser->polls()->create([
            'title' => 'Sondage d\'un autre créateur',
            'organizer_name' => $otherUser->name,
            'allows_multiple_choices' => true,
            'slug' => 'sondage-autre-createur-abcde',
        ]);

        $response = $this->actingAs($owner)->get('/');

        $response->assertOk();
        $response->assertSee('Mon sondage privé');
        $response->assertDontSee("Sondage d'un autre créateur");
        $response->assertSee('Retrouver tes dernières créations');
    }
}
