<?php

namespace Tests\Feature\Livewire\Reports;

use App\Livewire\Reports\ReportIndex;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_admin_can_view_reports_page()
    {
        $admin = $this->admin();
        $reporter = User::factory()->create();

        Report::create([
            'reporter_id' => $reporter->id,
            'reason' => 'Conteúdo ofensivo',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(ReportIndex::class)
            ->assertOk()
            ->assertSee('Conteúdo ofensivo');
    }

    public function test_pending_report_is_listed()
    {
        $admin = $this->admin();
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create(['name' => 'João Silva']);

        Report::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $reportedUser->id,
            'reason' => 'Assédio',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(ReportIndex::class)
            ->assertSee('João Silva')
            ->assertSee('Assédio')
            ->assertSee('Pendente');
    }

    public function test_can_mark_report_as_resolved()
    {
        $admin = $this->admin();
        $reporter = User::factory()->create();

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reason' => 'Spam',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(ReportIndex::class)
            ->call('markResolved', $report->id);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);
    }

    public function test_can_ban_reported_user()
    {
        $admin = $this->admin();
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $reportedUser->id,
            'reason' => 'Comportamento abusivo',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(ReportIndex::class)
            ->call('banReportedUser', $report->id);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $reportedUser->id,
            'status' => 'banned',
        ]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);
    }

    public function test_can_delete_reported_content_and_resolve_report()
    {
        $admin = $this->admin();
        $reporter = User::factory()->create();
        $author = User::factory()->create();

        $post = Post::create([
            'user_id' => $author->id,
            'title' => 'Post ofensivo',
            'content' => 'Conteúdo problemático',
            'type' => 'post',
        ]);

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $author->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reason' => 'Conteúdo inadequado',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(ReportIndex::class)
            ->call('deleteReportable', $report->id);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);
    }

    public function test_regular_user_is_forbidden_from_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }
}
