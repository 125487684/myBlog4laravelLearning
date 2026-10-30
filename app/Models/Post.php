<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'body', 'user_id'];

    protected function casts(): array
    {
        return [
            'hidden_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 按访问者过滤可见文章，隐藏文章只有作者本人和管理员可见。
     * 所有列出文章的查询都经过词作用域，保证可见性规则只存在一份
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        // 管理员可见
        if ($user?->is_admin) {
            return $query;
        }

        // 登录用户：未隐藏 + 自己所有
        if ($user) {
            return $query->where(function (Builder $q) use ($user) {
                $q->whereNull('hidden_at')
                    ->orWhere('user_id', $user->id);
            });
        }

        // 访客只可见公开
        return $query->whereNull('hidden_at');
    }
}
