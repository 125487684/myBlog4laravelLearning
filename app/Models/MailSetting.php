<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'port' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? static::create([
            'host' => 'localhost',
            'port' => 1025,
            'encryption' => null,
            'username' => null,
            'password' => null,
            'from_address' => 'hello@example.com',
            'from_name' => 'Blog',
        ]);
    }
}
