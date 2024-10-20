<?php

namespace App\Models;

use App\Enums\ContactMessageStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/**
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string $message
 * @property-read ContactMessageStatus $status
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $confirmed_at
 * @property ?\Illuminate\Support\Carbon $expires_at
 *
 * @method static \Database\Factories\ContactMessageFactory factory($count = null, $state = [])
 */
class ContactMessage extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'message',
        'confirmed_at',
        'expires_at',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confirmed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'status',
    ];

    /**
     * Uses default expires at date/time for message.
     *
     * @return $this
     */
    public function useDefaultExpiresAt(): static
    {
        $this->expires_at = static::getDefaultExpiresAt();

        return $this;
    }

    /**
     * Generates URL to confirm message.
     *
     * @return string
     */
    public function generateUrl()
    {
        return URL::temporarySignedRoute('contact.confirm', $this->expires_at, ['contactMessage' => $this]);
    }

    /**
     * Gets the statud of the message
     */
    protected function status(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            isset($this->confirmed_at) => ContactMessageStatus::Confirmed,
            isset($this->expires_at) && $this->expires_at->isFuture() => ContactMessageStatus::Unconfirmed,
            isset($this->expires_at) && $this->expires_at->isPast() => ContactMessageStatus::Expired,
            default => ContactMessageStatus::Accepted
        });
    }

    /**
     * Gets the default expires at date/time
     *
     * @return \Illuminate\Support\Carbon
     */
    public static function getDefaultExpiresAt()
    {
        return now()->addHours(2);
    }
}
