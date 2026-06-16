<?php

namespace App\Policies;

use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Policies\CommentPolicy as CommendationsPolicy;

class CommentPolicy extends CommendationsPolicy
{
    public function create(Commenter $user): bool
    {
        return true;
    }

    public function update($user, Comment $comment): bool
    {
        return $user->roles->contains('id', 1);
    }

    public function delete($user, Comment $comment): bool
    {
        return $user->roles->contains('id', 1);
    }
}
