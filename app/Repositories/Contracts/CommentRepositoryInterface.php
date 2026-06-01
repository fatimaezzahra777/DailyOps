<?php

namespace App\Repositories\Contracts;

interface CommentRepositoryInterface
{
    public function store(array $data);

    public function delete($id);
}