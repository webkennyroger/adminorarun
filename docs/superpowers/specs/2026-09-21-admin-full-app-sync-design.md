# Admin 100% sincronizado com o app — Design Spec

## Contexto

O admin (Livewire/Volt + Flux + Tailwind v4) hoje gerencia Usuários, Atividades,
Desafios, Categorias, Metas, Planos de assinatura, Agenda e Suporte. O app
Flutter (Ora Run) tem 8 áreas de dados sem NENHUMA tela de gerenciamento:
Feed (Posts+Enquetes), Comentários, Curtidas, Clubes, Chat, Stories, Segments,
e Denúncias/Bloqueios. Todas as tabelas/models já existem no banco (migrations
já rodadas) — o trabalho é 100% de camada de admin (Livewire) + ativar
broadcasting em tempo real (Reverb já instalado, desligado) + um endpoint de
API que falta (`POST /report`, hoje só existe a tabela e o model `Report`,
sem controller/rota — por isso "Denunciar" no app só fecha o menu).

## Padrão a seguir (já estabelecido no código, módulo `Challenges`)

- Um componente Livewire "Index" monolítico por módulo: listagem + criar +
  editar + excluir, tudo via modais Blade customizados (`<div class="fixed
  inset-0 bg-black/50">`, **não** `<flux:modal>` — o projeto usa Flux pouco).
- `WithPagination`, busca via `wire:model.live.debounce.300ms`, seleção em
  massa (`$selected`, `deleteSelected()`).
- Toast: `$this->dispatch('toast', ['type' => ..., 'message' => ...])`.
- Rota: `Route::get('/admin/<modulo>', <Modulo>Index::class)->name('admin.<modulo>.index')`
  dentro do grupo `['auth', 'check.admin.or.manager']` em `routes/web.php`.
- Menu: item novo em `App\Helpers\MenuHelper::getMainNavItems()` (dentro do
  bloco `isAdmin()||isManager()`), ícone novo em `getIconSvg()` se precisar.

## Tema

Trocar a escala `--color-brand-*` (hoje azul, `#465fff`) por uma escala verde
derivada de `#16B83E` (a cor "ORA RUN" do app mobile) no bloco `@theme` de
`resources/css/app.css`. Os utilitários `bg-brand-*`, `text-brand-*`,
`ring-brand-*`, `menu-item-active` etc. já usam essa escala em todo o admin —
trocar só essa escala já aplica a cor em tudo, sem precisar tocar em cada
view. Manter as variáveis `--mere-*` como estão (já são verdes/compatíveis).
Responsivo: a sidebar já colapsa via Alpine `$store.sidebar` — não precisa
trabalho novo, só confirmar que os módulos novos não quebram isso (usar os
mesmos componentes de layout dos módulos existentes).

## Tempo real

Ativar Reverb: `BROADCAST_CONNECTION=reverb` (hoje `null`/`log`), confirmar
`resources/js/echo.js` é importado em `resources/js/app.js` (o agente de
pesquisa não confirmou isso — checar e corrigir se faltar). Eventos novos:
`ReportCreated` (broadcast on: canal privado `admin.notifications`, ouvido
pelo card de "Denúncias pendentes" do dashboard e por um badge no item de
menu "Denúncias"). Sem broadcasting em cada módulo — só onde há valor real
(contador de denúncias pendentes, badge de novas mensagens de suporte já
existente é o precedente).

## Módulos novos

1. **Feed (Posts + Enquetes)** — tabela `posts` (campo `type`: post/poll).
   Listagem unificada com filtro por tipo, ver conteúdo/mídia, excluir
   (soft delete se o model suportar, senão delete direto — checar). Enquetes
   mostram contagem de votos por opção (`poll_options`, `votes_count`).
2. **Comentários** — tabela `comments` (polimórfico `commentable`). Listagem
   global com coluna "em quê" (post/atividade/enquete, via `commentable_type`),
   trecho do texto, autor, excluir.
3. **Clubes** — CRUD completo (`clubs` + pivot `club_user`): criar/editar/
   excluir clube, ver/gerenciar membros (promover a admin do clube, remover
   membro).
4. **Chat** — somente oversight (`messages`, `chat_groups`/`group_messages`):
   listar conversas por atividade recente, abrir thread (somente leitura),
   excluir mensagem em caso de abuso.
5. **Stories** — listar (ativas primeiro, expiradas depois — `expires_at`),
   ver mídia, excluir.
6. **Segments** — listar (`segments`), ver leaderboard (`segment_efforts`
   ordenado por `duration_seconds`), excluir segmento inválido/duplicado.
7. **Denúncias/Moderação** — depende da Task de backend abaixo. Tela lista
   denúncias (pendente/resolvida), mostra o que foi denunciado (usuário,
   post, comentário), motivo, permite marcar como resolvida ou banir/excluir
   o conteúdo/usuário direto da tela.
8. **Dashboard**: card "Denúncias pendentes" (contagem + tempo real via
   Reverb), seguindo o padrão visual dos cards existentes
   (`Dashboard/Stats/Stats.php`).

## Backend: endpoint de denúncia (pré-requisito do módulo 7)

`Report` hoje só tem `reporter_id`/`reported_user_id` — não dá pra denunciar
um post/comentário especificamente, só um usuário. Migration nova (aditiva,
não quebra nada): `reportable_type`/`reportable_id` (nullable) na tabela
`reports`. Endpoint `POST /report` (novo `ReportController`, autenticado):
recebe `{ reportable_type: 'post'|'comment'|null, reportable_id, reported_user_id, reason, details }`.
Se vier `reportable_type`, preenche o morph; sempre preenche
`reported_user_id` (autor do conteúdo, resolvido no backend a partir do
conteúdo denunciado quando aplicável).

## Fora de escopo desta spec

- Wiring do lado Flutter (`post_card.dart`/`poll_card.dart` chamarem o
  endpoint novo de denúncia) — é no repo `ora`, feito como tarefa separada
  depois que o endpoint existir.
- Migração para Supabase — projeto totalmente separado, já discutido e
  adiado (`RESUMO_TAREAFS.md` no repo `ora`).
