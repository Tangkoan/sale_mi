<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Import សម្រាប់​ប្រើ Permission
use Spatie\Permission\Traits\HasRoles; // ១. Import អាមួយនេះ

// 1. ហៅមកប្រើ
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;
    use LogsActivity; // 2. ដាក់ Trait ចូល

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar', // បន្ថែមពាក្យនេះ
        'theme_settings', // ១. បន្ថែមបន្ទាត់នេះ ដើម្បីឱ្យអាច Save ចូល Database បាន
        'pin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'theme_settings' => 'array', // ២. បន្ថែមបន្ទាត់នេះ ដើម្បីឱ្យ Laravel ដឹងថាវាជា JSON (Array)
        ];
    }


    // 3. កំណត់ Option (ថាតើចង់ Log អ្វីខ្លះ?)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'email', 'role']) // Log តែ field សំខាន់ៗ
        ->logOnlyDirty() // Log តែ field ណាដែលកែប្រែ (បើចុច Save តែអត់កែអ្វីសោះ វាមិន Log ទេ)
        ->dontSubmitEmptyLogs();
    }
}
