<?php

namespace App\Models\Collections;

use App\Models\Article;
use App\Traits\Support\HasWeights;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Collection<int, Article>
 */
class ArticleCollection extends Collection
{
    use HasWeights;

    /**
     * Gets articles with most comments
     *
     * @return static
     */
    public function popular()
    {
        return $this->sortBy(fn (Article $article) => $article->comments()->approved()->count());
    }

    /**
     * Gets articles grouped by date/time format
     */
    public function groupedByDateTime(string $format)
    {
        return $this->mapToGroups(fn (Article $article) => [$article->published_at->format($format) => $article]);
    }

    /**
     * Gets articles with any of tags
     *
     * @param array $tags
     * @return static
     */
    public function withTags(array $tags)
    {
        return $this->mapToWeight(function (Article $article) use ($tags) {
            return $article->tags->whereIn('slug', $tags)->count();
        });
    }

    /**
     * Gets articles that contains any of the keywords
     *
     * @param array $keywords
     * @param bool $ignoreCase
     * @return static
     */
    public function withKeywords(array $keywords, bool $ignoreCase = true)
    {
        $keywords = $ignoreCase ? Arr::map($keywords, fn ($keyword) => Str::lower($keyword)) : $keywords;

        return $this->mapToWeight(function (Article $article) use ($keywords, $ignoreCase) {
            $compiled = Str::of(Str::stripTags($article->revision->compiled));

            if ($ignoreCase) {
                $compiled = $compiled->lower();
            }

            $found = 0;

            foreach ($keywords as $keyword) {
                $found += $compiled->substrCount($keyword) + Str::substrCount(Str::lower($article->title), $keyword);

                foreach ($article->comments as $comment) {
                }
            }

            return $found;
        });
    }
}
