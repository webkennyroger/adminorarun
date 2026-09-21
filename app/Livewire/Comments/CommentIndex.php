<?php

namespace App\Livewire\Comments;

use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class CommentIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    public $selected = [];

    public $selectAll = false;

    public $confirmingDeletion = false;

    public $commentId;

    protected $queryString = ['search'];

    private function getCommentsQuery()
    {
        return Comment::with(['user', 'commentable', 'parent.user'])
            ->when($this->search, function ($query) {
                $query->where('body', 'like', '%'.$this->search.'%');
            })
            ->latest();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function toggleSelectAll()
    {
        // Ensure $selected is always an array
        if (! is_array($this->selected)) {
            $this->selected = [];
        }

        $perPage = $this->perPage == -1 ? 100000 : $this->perPage;

        if (count($this->selected) > 0) {
            // If any are selected, deselect all
            $this->selected = [];
            $this->selectAll = false;
        } else {
            // Select all on current page
            $this->selected = $this->getCommentsQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->commentId = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->commentId = null;
    }

    public function delete()
    {
        if ($this->commentId) {
            // The comments table has parent_id -> comments.id with onDelete('cascade')
            // at the database level (see 2025_12_12_000000_create_social_interactions_tables.php),
            // AND the Comment model's booted() deleting hook already walks $comment->replies()
            // and calls delete() on each one before removing the parent. That model-level
            // recursion is what actually matters here: it makes sure every reply's own
            // deleting event fires too, so likes attached to replies are cleaned up as well
            // (likes are a polymorphic table with no FK, so DB cascade alone would never
            // reach them). Calling Eloquent's delete() (not a raw query delete) is what
            // keeps that event chain intact — replies are deleted in cascade, never left
            // orphaned.
            Comment::findOrFail($this->commentId)->delete();
            $this->confirmingDeletion = false;
            $this->commentId = null;
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'O comentário (e suas respostas) foi removido do sistema!',
                'title' => 'Comentário excluído',
            ]);
        }
    }

    public function deleteSelected()
    {
        // Comment::destroy() loads each model and calls ->delete() on it individually,
        // which keeps the booted() deleting hook (cascade of replies + likes) firing for
        // every selected comment — unlike Comment::whereIn(...)->delete(), which would
        // bypass model events entirely and leave replies/likes orphaned.
        Comment::destroy($this->selected);
        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Comentários selecionados foram excluídos com sucesso!']);
    }

    public function render()
    {
        if ($this->perPage == -1) {
            $comments = $this->getCommentsQuery()->get();

            $comments = new LengthAwarePaginator(
                $comments,
                $comments->count(),
                $comments->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $comments = $this->getCommentsQuery()->paginate($this->perPage);
        }

        return view('livewire.comments.comment-index', [
            'comments' => $comments,
        ]);
    }
}
