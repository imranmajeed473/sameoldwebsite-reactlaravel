<?php

namespace App\Models;

use App\Models\Collections\ArticleCollection;
use App\Traits\Models\Postable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class Article extends Model
{
    use HasFactory;
    use Postable;
    use SoftDeletes;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'slug',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'mainImage',
        'currentRevision',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'revision',
    ];

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new ArticleCollection($models);
    }

    /**
     * Gets the current revision
     *
     * @return mixed
     */
    public function currentRevision()
    {
        return $this->belongsTo(Revision::class, 'current_revision');
    }

    /**
     * Gets the revisions of this article
     *
     * @return mixed
     */
    public function revisions()
    {
        return $this->hasMany(Revision::class);
    }

    /**
     * Gets the tags that this article has.
     *
     * @return mixed
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Gets comments for this article.
     *
     * @return mixed
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Gets images for this article.
     *
     * @return mixed
     */
    public function images()
    {
        return $this->hasMany(ArticleImage::class);
    }

    /**
     * Gets the main image (if any)
     *
     * @return mixed
     */
    public function mainImage()
    {
        return $this->belongsTo(ArticleImage::class, 'main_image', 'uuid');
    }

    /**
     * Scope a query to only include published articles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Scope a query to sort by published_at column value.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortedByPublishDate($query)
    {
        return $query->latest('published_at');
    }

    /**
     * Gets the revision for this article.
     *
     * @return Attribute
     */
    protected function revision(): Attribute
    {
        return Attribute::get(fn () => ! is_null($this->currentRevision) ? $this->currentRevision : $this->revisions()->latest()->first());
    }

    /**
     * Checks if article is published.
     *
     * @return Attribute
     */
    protected function isPublished(): Attribute
    {
        return Attribute::get(fn () => ! is_null($this->published_at) && $this->published_at->isPast());
    }

    /**
     * Checks if article is scheduled to be published.
     *
     * @return Attribute
     */
    protected function isScheduled(): Attribute
    {
        return Attribute::get(fn () => ! is_null($this->published_at) && $this->published_at->isFuture());
    }

    /**
     * Get the URL for the article.
     *
     * @return Attribute
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => $this->is_published ? $this->public_url : $this->private_url);
    }

    /**
     * Get the public URL for the article.
     *
     * @return Attribute
     */
    protected function publicUrl(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => $this->createPublicLink());
    }

    /**
     * Get the private URL for the article.
     *
     * @return Attribute
     */
    protected function privateUrl(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => $this->createPrivateUrl());
    }

    /**
     * Creates public link to this article
     *
     * @param bool $absolute
     * @param array $params Any extra parameters to include in URL
     * @return string
     */
    public function createPublicLink(bool $absolute = true, array $params = [])
    {
        return URL::route('blog.single', [...$params, 'article' => $this], $absolute);
    }

    /**
     * Creates temporary signed URL to this article
     *
     * @param int $minutes Minutes until URL expires (default: 30)
     * @param bool $absolute If true, absolute URL is returned. (default: true)
     * @param array $params Any extra parameters to include in URL
     * @return string
     */
    public function createPrivateUrl(int $minutes = 30, bool $absolute = true, array $params = [])
    {
        return URL::temporarySignedRoute('blog.preview', $minutes * 60, [...$params, 'article' => $this], $absolute);
    }
}
