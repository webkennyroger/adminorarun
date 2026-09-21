<?php

namespace App\Livewire\Feed;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class FeedIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $typeFilter = '';

    public $perPage = 10;

    public $selected = [];

    public $selectAll = false;

    // Modal states
    public $showViewModal = false;

    public $confirmingDeletion = false;

    public $selectedPost = null;

    public $postId;

    protected $queryString = ['search', 'typeFilter'];

    private function getPostsQuery()
    {
        return Post::with('user')
            ->withCount(['likes', 'comments'])
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('content', 'like', '%'.$this->search.'%');
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->latest();
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
            $this->selected = $this->getPostsQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function deleteSelected()
    {
        Post::whereIn('id', $this->selected)->get()->each->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Publicações selecionadas foram excluídas com sucesso!']);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function view($id)
    {
        $this->selectedPost = Post::with(['user', 'pollOptions'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedPost = null;
    }

    public function confirmDelete($id)
    {
        $this->postId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->postId) {
            // Post::booted() already cascades comments, likes, poll options and poll votes.
            Post::findOrFail($this->postId)->delete();
            $this->confirmingDeletion = false;
            $this->postId = null;
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'A publicação foi removida do sistema!',
                'title' => 'Publicação excluída',
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->postId = null;
    }

    public function render()
    {
        if ($this->perPage == -1) {
            $posts = $this->getPostsQuery()->get();

            $posts = new LengthAwarePaginator(
                $posts,
                $posts->count(),
                $posts->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $posts = $this->getPostsQuery()->paginate($this->perPage);
        }

        return view('livewire.feed.feed-index', [
            'posts' => $posts,
        ]);
    }
}
