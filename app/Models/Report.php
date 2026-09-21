<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reported_message_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'details',
        'status',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reportedMessage()
    {
        return $this->belongsTo(Message::class, 'reported_message_id');
    }

    /**
     * Conteúdo denunciado (post, enquete ou comentário) quando a denúncia
     * não é diretamente sobre um usuário/mensagem.
     */
    public function reportable()
    {
        return $this->morphTo();
    }
}
