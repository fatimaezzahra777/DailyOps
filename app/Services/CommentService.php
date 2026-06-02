<?php

namespace App\Services;

use App\Repositories\Contracts\CommentRepositoryInterface;

class CommentService
{
    protected $commentRepository;

    public function __construct(CommentRepositoryInterface $commentRepository) {
        $this->commentRepository = $commentRepository;
    }

    public function createComment(array $data)
    {
        return $this->commentRepository->store($data);
    }

    public function deleteComment($id)
    {
        return $this->commentRepository->delete($id);
    }
}