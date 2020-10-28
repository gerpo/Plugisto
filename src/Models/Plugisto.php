<?php

namespace Gerpo\Plugisto\Models;

use Gerpo\Plugisto\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;

class Plugisto extends Model
{
    protected $table = 'plugisto';

    protected $fillable = [
        'name',
        'description',
        'route',
        'namespace',
        'is_active',
        'manually_added',
        'needed_permission',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'manually_added' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ActiveScope());
    }

    public function activate(): void
    {
        $this->update([
            'is_active' => true,
        ]);
    }

    public function deactivate(): void
    {
        $this->update([
            'is_active' => false,
        ]);
    }

    public function scopeAllowed($query, array $permissions)
    {
        array_push($permissions, null);

        return $query->whereIn('needed_permission', $permissions)->orWhereNull('needed_permission');
    }
}
