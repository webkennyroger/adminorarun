<?php

namespace App\Livewire\Reports;

use App\Models\Report;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ReportIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $perPage = 10;

    // Modal state
    public $showViewModal = false;

    public $selectedReport = null;

    protected $queryString = ['search', 'statusFilter'];

    private function getReportsQuery()
    {
        return Report::with(['reporter', 'reportedUser.profile', 'reportedMessage', 'reportable'])
            ->when($this->search, function ($query) {
                $query->where('reason', 'like', '%'.$this->search.'%')
                    ->orWhere('details', 'like', '%'.$this->search.'%')
                    ->orWhereHas('reporter', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            // Denúncias pendentes sempre primeiro, depois as mais recentes.
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest();
    }

    /**
     * Resumo do que foi denunciado, pra exibir na listagem.
     * Prioridade: conteúdo específico (post/comentário) > mensagem > usuário,
     * porque um post/comentário denunciado também carrega o autor em
     * reported_user_id (ver ReportController), e o conteúdo é o que importa.
     */
    public function reportedContentLabel(Report $report): string
    {
        if ($report->reportable_type) {
            $type = class_basename($report->reportable_type);

            if ($report->reportable) {
                return match ($type) {
                    'Post' => 'Post: '.Str::limit($report->reportable->title ?: strip_tags((string) $report->reportable->content), 40),
                    'Comment' => 'Comentário: '.Str::limit(strip_tags((string) $report->reportable->body), 40),
                    default => $type,
                };
            }

            return match ($type) {
                'Post' => 'Post (conteúdo excluído)',
                'Comment' => 'Comentário (conteúdo excluído)',
                default => $type.' (excluído)',
            };
        }

        if ($report->reported_message_id) {
            return 'Mensagem';
        }

        if ($report->reportedUser) {
            return 'Usuário: '.$report->reportedUser->name;
        }

        return 'Não especificado';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function view($id)
    {
        $this->selectedReport = Report::with(['reporter', 'reportedUser.profile', 'reportedMessage', 'reportable'])
            ->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedReport = null;
    }

    /**
     * Marca a denúncia como resolvida sem tomar nenhuma outra ação —
     * uso para denúncias improcedentes.
     */
    public function markResolved($id)
    {
        $report = Report::findOrFail($id);
        $report->update(['status' => 'resolved']);

        $this->afterAction('Denúncia marcada como resolvida.');
    }

    /**
     * Exclui o conteúdo denunciado (post/comentário) e resolve a denúncia.
     */
    public function deleteReportable($id)
    {
        $report = Report::with('reportable')->findOrFail($id);

        if ($report->reportable) {
            $report->reportable->delete();
        }

        $report->update(['status' => 'resolved']);

        $this->afterAction('Conteúdo denunciado foi excluído e a denúncia foi resolvida.');
    }

    /**
     * Bane o usuário denunciado (profile.status = banned) e resolve a denúncia.
     */
    public function banReportedUser($id)
    {
        $report = Report::with('reportedUser.profile')->findOrFail($id);

        if ($report->reportedUser && $report->reportedUser->profile) {
            $report->reportedUser->profile->update(['status' => 'banned']);
        }

        $report->update(['status' => 'resolved']);

        $this->afterAction('Usuário denunciado foi banido e a denúncia foi resolvida.');
    }

    private function afterAction(string $message)
    {
        $this->showViewModal = false;
        $this->selectedReport = null;

        $this->dispatch('toast', ['type' => 'success', 'message' => $message]);
    }

    public function render()
    {
        if ($this->perPage == -1) {
            $reports = $this->getReportsQuery()->get();

            $reports = new LengthAwarePaginator(
                $reports,
                $reports->count(),
                $reports->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $reports = $this->getReportsQuery()->paginate($this->perPage);
        }

        return view('livewire.reports.report-index', [
            'reports' => $reports,
        ]);
    }
}
