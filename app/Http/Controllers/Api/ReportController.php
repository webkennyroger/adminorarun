<?php

namespace App\Http\Controllers\Api;

use App\Events\ReportCreated;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Cria uma denúncia de post/enquete, comentário ou usuário.
     *
     * O app envia `type` + `id` do conteúdo (ou do usuário, quando
     * `type` for "user"); o autor do conteúdo é resolvido aqui, não
     * confiamos em nada que o cliente mande sobre quem é o denunciado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:post,poll,comment,user',
            'id' => 'required|integer',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:2000',
        ]);

        $reportable = null;
        $reportedUserId = null;

        switch ($validated['type']) {
            case 'post':
            case 'poll':
                $reportable = Post::findOrFail($validated['id']);
                $reportedUserId = $reportable->user_id;
                break;
            case 'comment':
                $reportable = Comment::findOrFail($validated['id']);
                $reportedUserId = $reportable->user_id;
                break;
            case 'user':
                $reportedUserId = $validated['id'];
                break;
        }

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reported_user_id' => $reportedUserId,
            'reportable_type' => $reportable?->getMorphClass(),
            'reportable_id' => $reportable?->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        $pendingCount = Report::where('status', 'pending')->count();
        event(new ReportCreated($report, $pendingCount));

        return response()->json(['success' => true, 'id' => $report->id], 201);
    }
}
