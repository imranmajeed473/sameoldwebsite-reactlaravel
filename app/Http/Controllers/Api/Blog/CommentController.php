<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'show' => [
                'sometimes',
                Rule::in(['awaiting', 'approved', 'denied', 'all']),
            ],
            'article' => [
                'sometimes',
                'numeric',
                Rule::exists(Article::class, 'id'),
            ],
            'user' => [
                'sometimes',
                'numeric',
                Rule::exists(User::class, 'id'),
            ],
        ]);

        $query = Comment::with(['approvedBy', 'post', 'post.user']);

        $show = (string) $request->str('show', 'all');

        if ($show === 'awaiting') {
            $query = $query->whereHas('post', function (Builder $query) use ($request) {
                $query->whereNull('posts.deleted_at');
            })->whereNull('approved_at');
        } elseif ($show === 'approved') {
            $query = $query->whereHas('post', function (Builder $query) use ($request) {
                $query->whereNull('posts.deleted_at');
            })->whereNotNull('approved_at');
        } elseif ($show === 'denied') {
            $query = $query->whereHas('post', function (Builder $query) use ($request) {
                $query->whereNotNull('posts.deleted_at');
            })->whereNull('approved_at');
        }

        if ($request->has('article')) {
            $query = $query->where('article_id', $request->integer('article'));
        }

        if ($request->has('user')) {
            $query = $query->whereHas('post', function (Builder $query) use ($request) {
                $query->where('posts.user_id', $request->integer('user'));
            });
        }

        return new CommentCollection($query->paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        return $comment->load(['approvedBy', 'post.user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            /*'parent' => [
                'nullable',
                'numeric',
                Rule::exists(Comment::class, 'id')->where(function (Builder $query) use ($comment) {
                    return $query->where('id', '<>', $comment->getKey());
                })
            ]*/
        ]);

        $comment->title = $request->str('title');
        $comment->comment = $request->str('comment');

        /*if ($request->filled('parent'))
            $comment->parent()->associate($request->parent);
        else
            $comment->parent()->disassociate();*/

        $comment->save();

        return $comment;
    }

    public function approve(Request $request, Comment $comment)
    {
        $request->validate([
            'approved_at' => 'nullable|date',
        ]);

        $comment->approved_at = $request->date('approved_at') ?? now();
        $comment->approvedBy()->associate($request->user());

        if ($comment->post->trashed()) {
            $comment->post->restore();
        }

        $comment->save();

        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->post->delete();

        return [
            'success' => __('Comment was removed.'),
        ];
    }
}
