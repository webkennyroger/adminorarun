<?php

namespace Tests\Feature\Livewire\Segments;

use App\Livewire\Segments\SegmentIndex;
use App\Models\Activity;
use App\Models\Segment;
use App\Models\SegmentEffort;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SegmentIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_segments_page()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        Segment::factory()->create(['name' => 'Volta do Parque']);

        $this->actingAs($user)
            ->get(route('admin.segments.index'))
            ->assertOk()
            ->assertSee('Volta do Parque');
    }

    public function test_search_filters_segments_by_name()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        Segment::factory()->create(['name' => 'Volta do Parque']);
        Segment::factory()->create(['name' => 'Subida da Serra']);

        Livewire::actingAs($user)
            ->test(SegmentIndex::class)
            ->set('search', 'Parque')
            ->assertSee('Volta do Parque')
            ->assertDontSee('Subida da Serra');
    }

    public function test_can_delete_segment_and_its_efforts()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        $segment = Segment::factory()->create(['name' => 'Volta do Parque']);
        $activity = Activity::factory()->create();
        $effort = SegmentEffort::factory()->create([
            'segment_id' => $segment->id,
            'activity_id' => $activity->id,
        ]);

        Livewire::actingAs($user)
            ->test(SegmentIndex::class)
            ->call('confirmDelete', $segment->id)
            ->call('delete');

        $this->assertDatabaseMissing('segments', ['id' => $segment->id]);
        $this->assertDatabaseMissing('segment_efforts', ['id' => $effort->id]);
    }

    public function test_leaderboard_shows_efforts_ordered_by_fastest_time()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        $segment = Segment::factory()->create(['name' => 'Volta do Parque']);
        $fastRunner = User::factory()->create(['name' => 'Fast Runner']);
        $slowRunner = User::factory()->create(['name' => 'Slow Runner']);

        SegmentEffort::factory()->create([
            'segment_id' => $segment->id,
            'activity_id' => Activity::factory()->create()->id,
            'user_id' => $slowRunner->id,
            'duration_seconds' => 200,
        ]);
        SegmentEffort::factory()->create([
            'segment_id' => $segment->id,
            'activity_id' => Activity::factory()->create()->id,
            'user_id' => $fastRunner->id,
            'duration_seconds' => 100,
        ]);

        Livewire::actingAs($user)
            ->test(SegmentIndex::class)
            ->call('viewLeaderboard', $segment->id)
            ->assertSet('showLeaderboardModal', true)
            ->assertSeeInOrder(['Fast Runner', 'Slow Runner']);
    }
}
