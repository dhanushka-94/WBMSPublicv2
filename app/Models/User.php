<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'profile_photo',
        'email',
        'password',
        'role',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            $user->deleteProfilePhotoFile();
        });
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            $version = Storage::disk('public')->lastModified($this->profile_photo);

            return asset('storage/'.$this->profile_photo).'?v='.$version;
        }

        $letter = strtoupper(mb_substr($this->name ?: 'U', 0, 1));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128"><rect width="128" height="128" fill="#2563eb"/><text x="64" y="78" text-anchor="middle" fill="#ffffff" font-size="64" font-family="Arial,sans-serif">'.$letter.'</text></svg>';

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }

    public function syncProfilePhoto(?UploadedFile $file, bool $remove = false): void
    {
        if ($file) {
            $this->deleteProfilePhotoFile();
            $this->profile_photo = $file->store('user-photos', 'public');

            return;
        }

        if ($remove) {
            $this->deleteProfilePhotoFile();
            $this->profile_photo = null;
        }
    }

    public function deleteProfilePhotoFile(): void
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            Storage::disk('public')->delete($this->profile_photo);
        }
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is meter reader
     */
    public function isMeterReader(): bool
    {
        return $this->role === 'meter_reader';
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'meter_reader' => 'Meter Reader',
            'billing_officer' => 'Billing Officer',
            'cashier' => 'Cashier',
            default => ucfirst($this->role),
        };
    }

    /**
     * Get status display
     */
    public function getStatusDisplayAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function routeDivisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'reader_route_divisions')->withTimestamps();
    }

    public function routeCustomers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'reader_route_customers')->withTimestamps();
    }

    public function hasRouteAssignment(): bool
    {
        return $this->routeDivisions()->exists() || $this->routeCustomers()->exists();
    }

    /**
     * Active customers on this reader's daily route.
     * An empty assignment means the reader receives nobody.
     */
    public function assignedRouteCustomers()
    {
        $divisionIds = $this->routeDivisions()->pluck('divisions.id');
        $customerIds = $this->routeCustomers()->pluck('customers.id');

        $query = Customer::query()->where('status', 'active');

        if ($divisionIds->isEmpty() && $customerIds->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($q) use ($divisionIds, $customerIds) {
            if ($divisionIds->isNotEmpty()) {
                $q->whereIn('division_id', $divisionIds);
            }

            if ($customerIds->isNotEmpty()) {
                $divisionIds->isNotEmpty()
                    ? $q->orWhereIn('id', $customerIds)
                    : $q->whereIn('id', $customerIds);
            }
        });
    }
}
