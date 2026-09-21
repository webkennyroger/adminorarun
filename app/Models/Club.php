<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'name',
        'description',
        'city',
        'state',
        'image',
        'avatar',
        'category',
        'is_public',
        'creator_id',
        'creator_name',
        'members_count',
        'followers_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'members_count' => 'integer',
        'followers_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isMember($userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    public function getMemberRole($userId): ?string
    {
        $pivot = $this->members()->where('user_id', $userId)->first();

        return $pivot?->pivot?->role;
    }
}
