<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorApplication extends Model
{
    protected $guarded = [];

    protected $attributes = ['status' => self::STATUS_NEW];

    protected function casts(): array
    {
        return [
            'declaration_confidentiality' => 'boolean',
            'declaration_source_of_funds' => 'boolean',
        ];
    }

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_SPAM = 'spam';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW         => 'New',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED    => 'Resolved',
            self::STATUS_SPAM        => 'Spam',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst($this->status);
    }
}
