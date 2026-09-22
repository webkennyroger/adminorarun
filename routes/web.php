<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\BillingController;
use App\Livewire\Activities\ActivityList;
use App\Livewire\Categories\CategoryIndex;
use App\Livewire\Challenges\ChallengeIndex;
use App\Livewire\Chat\ChatIndex;
use App\Livewire\Clubs\ClubIndex;
use App\Livewire\Comments\CommentIndex;
use App\Livewire\Feed\FeedIndex;
use App\Livewire\Goals\GoalIndex;
use App\Livewire\Plans\PlanIndex;
use App\Livewire\Profile\UserProfileEdit;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Schedule\ScheduleIndex;
use App\Livewire\Segments\SegmentIndex;
use App\Livewire\Stories\StoryIndex;
use App\Livewire\Support\SupportIndex;
use App\Livewire\Support\SupportList;
use App\Livewire\Support\SupportShow;
use App\Livewire\Users\UserIndex;
use App\Models\Schedule;
use App\Models\Support;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Rota de Autenticação Google (Socialite / Web)
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')
        ->scopes(['openid', 'email', 'profile'])
        ->redirect();
})->name('auth.google');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleCallback'])->name('auth.google.callback');

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified', 'check.admin.or.manager'])
    ->name('dashboard');

// Esta aplicação web é exclusiva para administradores e gerentes.
// Usuários comuns usam o aplicativo Ora (mobile), que consome a API em routes/api.php.
Route::middleware(['auth', 'check.admin.or.manager'])->group(function () {
    // Rota da Agenda/Calendário
    Route::get('/schedule', ScheduleIndex::class)->name('schedule.index');

    // API routes for calendar events
    Route::get('/api/events', function () {
        return response()->json(
            Schedule::where('user_id', auth()->id())
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->event_date->format('Y-m-d'),
                        'end' => $event->event_date->format('Y-m-d'),
                        'extendedProps' => [
                            'calendar' => $event->color ?? 'Primary',
                            'description' => $event->description,
                            'photo' => $event->photo,
                            'time' => $event->event_time,
                        ],
                    ];
                })
        );
    })->name('api.events.index');

    Route::post('/api/events', function (Request $request) {
        $data = [
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description ?? '',
            'event_date' => $request->start_date,
            'event_time' => $request->event_time ?? '00:00',
            'color' => $request->event_level ?? 'Primary',
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('events', 'public');
        }

        $event = Schedule::create($data);

        return response()->json(['id' => $event->id, 'success' => true]);
    })->name('api.events.store');

    Route::put('/api/events/{id}', function (Request $request, $id) {
        $event = Schedule::where('user_id', auth()->id())->findOrFail($id);

        $data = [
            'title' => $request->title,
            'description' => $request->description ?? '',
            'event_date' => $request->start_date,
            'event_time' => $request->event_time ?? '00:00',
            'color' => $request->event_level ?? 'Primary',
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('events', 'public');
        }

        $event->update($data);

        return response()->json(['success' => true]);
    })->name('api.events.update');

    Route::delete('/api/events/{id}', function ($id) {
        $event = Schedule::where('user_id', auth()->id())->findOrFail($id);
        $event->delete();

        return response()->json(['success' => true]);
    })->name('api.events.destroy');

    // Rota de Gerenciamento de Desafios Mensais (Admin)
    Route::get('/admin/challenges', ChallengeIndex::class)->name('admin.challenges.index');

    // Sincronização total com o app — feed, moderação e comunidade (Admin)
    Route::get('/admin/feed', FeedIndex::class)->name('admin.feed.index');
    Route::get('/admin/comments', CommentIndex::class)->name('admin.comments.index');
    Route::get('/admin/clubs', ClubIndex::class)->name('admin.clubs.index');
    Route::get('/admin/chat', ChatIndex::class)->name('admin.chat.index');
    Route::get('/admin/stories', StoryIndex::class)->name('admin.stories.index');
    Route::get('/admin/segments', SegmentIndex::class)->name('admin.segments.index');
    Route::get('/admin/reports', ReportIndex::class)->name('admin.reports.index');

    // Rota de Gerenciamento de Categorias
    Route::get('/categories', CategoryIndex::class)->name('categories.index');
    Route::view('/categories/create', 'livewire.categories.create')->name('categories.create');
    Route::get('/categories/{category}/edit', function ($category) {
        return view('livewire.categories.edit', compact('category'));
    })->name('categories.edit');

    // Goals (Metas)
    Route::get('/goals', GoalIndex::class)->name('goals.index');

    // Rota de Gerenciamento de Usuários
    Route::get('/users', UserIndex::class)->name('users.index');
    Route::get('/users/{user}', function (User $user) {
        return view('pages.profile.profile-index', compact('user'));
    })->name('users.show');

    // Subscription Plans Management
    Route::get('/plans', PlanIndex::class)->name('plans.index');

    // Rota de Gerenciamento de Atividades
    Route::get('/activities', ActivityList::class)->name('activities.index');

    // Rota para o componente Support/Index
    Route::get('/support', SupportIndex::class)->name('support.index');
    Route::post('/support', function (Request $request) {
        $request->validate([
            'subject' => 'required|min:5|max:100',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|min:10',
        ]);

        Support::create([
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'priority' => $request->priority,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return redirect()->route('support.list')->with('status', 'Ticket criado com sucesso!');
    })->name('support.store');

    Route::get('/support-list', SupportList::class)->name('support.list');

    Route::get('/support/{support}', SupportShow::class)->name('support.show');
    Route::post('/support/{support}/reply', function (Support $support, Request $request) {
        // Ensure user owns the ticket or is admin
        if ($support->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string|min:2',
        ]);

        $support->replies()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        return redirect()->route('support.show', $support)->with('message', 'Resposta enviada com sucesso!');
    })->name('support.reply');

    Route::patch('/support/{support}/status', function (Support $support, Request $request) {
        if (! auth()->user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:open,pending,resolved,closed',
        ]);

        $support->update(['status' => $request->status]);

        return redirect()->route('support.show', $support)->with('message', 'Status atualizado com sucesso!');
    })->name('support.update-status');
    // Rota de Redirecionamento de Suporte (Legado)
    Route::redirect('/support-show', '/support');

    // Rotas de faq
    Route::view('/faq', 'pages.faq')->name('faq');

    // Rotas de Termos e Privacidade
    Route::view('/terms', 'pages.terms')->name('terms.show');
    Route::view('/policy', 'pages.policy')->name('policy.show');

    // Rotas de coming
    Route::view('/coming', 'pages.coming-soon')->name('coming');

    // Profile Settings Routes
    Route::get('profile/edit', UserProfileEdit::class)->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Volt::route('settings/two-factor', 'settings.two-factor')
        ->name('two-factor.show')
        ->middleware('password.confirm');

    // Volt::route('/test-volt', function () {
    //     return 'Volt Routing Works';
    // });
});

// Billing / Minha Assinatura — reachable by any authenticated user (not just
// admin/manager), since regular users are redirected here after registering
// or logging in with Google from the otherwise admin-only web app.
Route::middleware(['auth'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/subscribe/{plan}', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});
