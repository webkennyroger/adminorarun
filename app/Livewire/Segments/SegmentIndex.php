<?php

namespace App\Livewire\Segments;

use App\Models\Segment;
use App\Models\SegmentEffort;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class SegmentIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $sportType = '';

    public $perPage = 10;

    public $selected = [];

    public $selectAll = false;

    // Leaderboard modal
    public $showLeaderboardModal = false;

    public $selectedSegment = null;

    // Delete confirmation
    public $confirmingDeletion = false;

    public $segmentId;

    protected $queryString = ['search'];

    private function getSegmentsQuery()
    {
        return Segment::with('creator')
            ->withCount('efforts')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->sportType, function ($query) {
                $query->where('sport_type', $this->sportType);
            })
            ->latest();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSportType()
    {
        $this->resetPage();
    }

    public function toggleSelectAll()
    {
        if (! is_array($this->selected)) {
            $this->selected = [];
        }

        $perPage = $this->perPage == -1 ? 100000 : $this->perPage;

        if (count($this->selected) > 0) {
            $this->selected = [];
            $this->selectAll = false;
        } else {
            $this->selected = $this->getSegmentsQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function deleteSelected()
    {
        SegmentEffort::whereIn('segment_id', $this->selected)->delete();
        Segment::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Segmentos selecionados foram excluídos com sucesso!']);
    }

    public function viewLeaderboard($id)
    {
        $this->selectedSegment = Segment::with('creator')
            ->withCount('efforts')
            ->findOrFail($id);

        $this->showLeaderboardModal = true;
    }

    public function closeLeaderboardModal()
    {
        $this->showLeaderboardModal = false;
        $this->selectedSegment = null;
    }

    public function confirmDelete($id)
    {
        $this->segmentId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->segmentId) {
            SegmentEffort::where('segment_id', $this->segmentId)->delete();
            Segment::findOrFail($this->segmentId)->delete();

            $this->confirmingDeletion = false;
            $this->segmentId = null;

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'O segmento foi removido do sistema!',
                'title' => 'Segmento excluído',
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->segmentId = null;
    }

    public function render()
    {
        $sportTypes = Segment::query()
            ->select('sport_type')
            ->distinct()
            ->orderBy('sport_type')
            ->pluck('sport_type');

        if ($this->perPage == -1) {
            $segments = $this->getSegmentsQuery()->get();

            $segments = new LengthAwarePaginator(
                $segments,
                $segments->count(),
                $segments->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $segments = $this->getSegmentsQuery()->paginate($this->perPage);
        }

        $leaderboard = collect();

        if ($this->selectedSegment) {
            $leaderboard = SegmentEffort::with('user')
                ->where('segment_id', $this->selectedSegment->id)
                ->orderBy('duration_seconds')
                ->get()
                ->values();
        }

        return view('livewire.segments.segment-index', [
            'segments' => $segments,
            'sportTypes' => $sportTypes,
            'leaderboard' => $leaderboard,
        ]);
    }
}
