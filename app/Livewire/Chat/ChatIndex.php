<?php

namespace App\Livewire\Chat;

use App\Models\ChatGroup;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Somente supervisão: o admin não participa das conversas, só audita.
 * Lista conversas (diretas 1:1 e de grupo) ordenadas pela mensagem mais
 * recente, permite abrir uma thread em modo leitura e excluir mensagens
 * pontuais em caso de abuso. Não há criação/edição de mensagens aqui.
 */
class ChatIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    protected $queryString = ['search'];

    // Thread modal state
    public $showThreadModal = false;

    public $threadType = null; // 'direct' | 'group'

    public $threadUserA = null;

    public $threadUserB = null;

    public $threadGroupId = null;

    public $threadTitle = null;

    public $threadMessages = [];

    // Delete confirmation state (inside the thread modal)
    public $confirmingMessageDeletion = false;

    public $deletingMessageId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Estratégia de agrupamento: mensagens 1:1 não têm uma "conversa" própria
     * no banco — cada linha é só um sender/receiver. Para agrupar "A->B" e
     * "B->A" na mesma conversa, normalizamos o par com CASE WHEN (menor id
     * primeiro) e usamos ROW_NUMBER() OVER (PARTITION BY par ORDER BY
     * created_at DESC) para pegar só a mensagem mais recente de cada par.
     * CASE WHEN (em vez de LEAST/GREATEST ou MIN/MAX de 2 colunas) e
     * ROW_NUMBER() funcionam tanto em MySQL 8+ (produção) quanto em SQLite
     * 3.25+ (usado nos testes), então a query é portável entre os dois.
     */
    private function latestDirectMessagesQuery()
    {
        $userAExpr = 'CASE WHEN sender_id < receiver_id THEN sender_id ELSE receiver_id END';
        $userBExpr = 'CASE WHEN sender_id < receiver_id THEN receiver_id ELSE sender_id END';

        $ranked = DB::table('messages')
            ->select([
                'id',
                'sender_id',
                'receiver_id',
                'content',
                'created_at',
                DB::raw("{$userAExpr} as user_a"),
                DB::raw("{$userBExpr} as user_b"),
                DB::raw("ROW_NUMBER() OVER (PARTITION BY {$userAExpr}, {$userBExpr} ORDER BY created_at DESC, id DESC) as rn"),
            ]);

        return DB::query()->fromSub($ranked, 'ranked')->where('rn', 1);
    }

    /**
     * Mesma estratégia de ROW_NUMBER(), mas partitionando por chat_group_id
     * (grupos já têm uma "conversa" natural, então não precisa normalizar
     * par de usuários).
     */
    private function latestGroupMessagesQuery()
    {
        $ranked = DB::table('group_messages')
            ->select([
                'id',
                'chat_group_id',
                'user_id',
                'content',
                'created_at',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY chat_group_id ORDER BY created_at DESC, id DESC) as rn'),
            ]);

        return DB::query()->fromSub($ranked, 'ranked')->where('rn', 1);
    }

    /**
     * Monta a lista unificada de "conversas" (diretas + grupo), já filtrada
     * pela busca e ordenada pela atividade mais recente. Feito em memória
     * (Collection) porque combina duas fontes (messages/group_messages) que
     * não podem ser unidas com um único paginate() do Eloquent de forma
     * simples.
     */
    private function getConversations(): Collection
    {
        $directRows = $this->latestDirectMessagesQuery()->get();

        $userIds = $directRows->flatMap(fn ($row) => [$row->user_a, $row->user_b])->unique()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $directConversations = $directRows->map(function ($row) use ($users) {
            $userA = $users->get($row->user_a);
            $userB = $users->get($row->user_b);

            return [
                'type' => 'direct',
                'key' => 'direct-'.$row->user_a.'-'.$row->user_b,
                'user_a_id' => $row->user_a,
                'user_b_id' => $row->user_b,
                'title' => ($userA?->name ?? 'Usuário #'.$row->user_a).' & '.($userB?->name ?? 'Usuário #'.$row->user_b),
                'searchable' => trim(($userA?->name ?? '').' '.($userB?->name ?? '')),
                'last_message' => $row->content,
                'last_at' => $row->created_at,
            ];
        });

        $groupRows = $this->latestGroupMessagesQuery()->get();

        $groupIds = $groupRows->pluck('chat_group_id')->unique()->values();
        $groups = ChatGroup::whereIn('id', $groupIds)->get()->keyBy('id');

        $groupConversations = $groupRows->map(function ($row) use ($groups) {
            $group = $groups->get($row->chat_group_id);

            return [
                'type' => 'group',
                'key' => 'group-'.$row->chat_group_id,
                'group_id' => $row->chat_group_id,
                'title' => 'Grupo: '.($group?->name ?? '#'.$row->chat_group_id),
                'searchable' => $group?->name ?? '',
                'last_message' => $row->content,
                'last_at' => $row->created_at,
            ];
        });

        $conversations = $directConversations->concat($groupConversations);

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $conversations = $conversations->filter(
                fn ($c) => str_contains(mb_strtolower($c['searchable']), $needle)
            );
        }

        return $conversations->sortByDesc('last_at')->values();
    }

    public function openThread(string $type, ?int $userA = null, ?int $userB = null, ?int $groupId = null)
    {
        $this->threadType = $type;
        $this->threadUserA = $userA;
        $this->threadUserB = $userB;
        $this->threadGroupId = $groupId;

        $this->loadThreadMessages();

        $this->showThreadModal = true;
    }

    private function loadThreadMessages(): void
    {
        if ($this->threadType === 'direct') {
            $messages = Message::with(['sender', 'receiver'])
                ->where(function ($query) {
                    $query->where('sender_id', $this->threadUserA)->where('receiver_id', $this->threadUserB);
                })
                ->orWhere(function ($query) {
                    $query->where('sender_id', $this->threadUserB)->where('receiver_id', $this->threadUserA);
                })
                ->latest()
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            $userA = User::find($this->threadUserA);
            $userB = User::find($this->threadUserB);
            $this->threadTitle = ($userA?->name ?? 'Usuário #'.$this->threadUserA).' & '.($userB?->name ?? 'Usuário #'.$this->threadUserB);

            $this->threadMessages = $messages->map(fn ($message) => [
                'id' => $message->id,
                'author' => $message->sender?->name ?? 'Usuário #'.$message->sender_id,
                'content' => $message->content,
                'created_at' => $message->created_at,
            ])->toArray();
        } elseif ($this->threadType === 'group') {
            $messages = GroupMessage::with('sender')
                ->where('chat_group_id', $this->threadGroupId)
                ->latest()
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            $group = ChatGroup::find($this->threadGroupId);
            $this->threadTitle = 'Grupo: '.($group?->name ?? '#'.$this->threadGroupId);

            $this->threadMessages = $messages->map(fn ($message) => [
                'id' => $message->id,
                'author' => $message->sender?->name ?? 'Usuário #'.$message->user_id,
                'content' => $message->content,
                'created_at' => $message->created_at,
            ])->toArray();
        }
    }

    public function closeThreadModal()
    {
        $this->showThreadModal = false;
        $this->threadType = null;
        $this->threadUserA = null;
        $this->threadUserB = null;
        $this->threadGroupId = null;
        $this->threadTitle = null;
        $this->threadMessages = [];
    }

    public function confirmMessageDeletion($messageId)
    {
        $this->deletingMessageId = $messageId;
        $this->confirmingMessageDeletion = true;
    }

    public function cancelMessageDeletion()
    {
        $this->confirmingMessageDeletion = false;
        $this->deletingMessageId = null;
    }

    /**
     * Exclusão de moderação: hard delete direto, sem soft delete. É uma ação
     * extrema (abuso), não um fluxo normal de edição de conteúdo.
     */
    public function deleteMessage()
    {
        if (! $this->deletingMessageId) {
            return;
        }

        if ($this->threadType === 'direct') {
            Message::where('id', $this->deletingMessageId)->delete();
        } elseif ($this->threadType === 'group') {
            GroupMessage::where('id', $this->deletingMessageId)->delete();
        }

        $this->confirmingMessageDeletion = false;
        $this->deletingMessageId = null;

        $this->loadThreadMessages();

        $this->dispatch('toast', [
            'type' => 'error',
            'title' => 'Mensagem excluída',
            'message' => 'A mensagem foi removida da conversa.',
        ]);
    }

    public function render()
    {
        $conversations = $this->getConversations();

        $page = $this->getPage();
        $perPage = $this->perPage == -1 ? max($conversations->count(), 1) : $this->perPage;

        $items = $conversations->forPage($page, $perPage)->values();

        $paginatedConversations = new LengthAwarePaginator(
            $items,
            $conversations->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.chat.chat-index', [
            'conversations' => $paginatedConversations,
        ]);
    }
}
