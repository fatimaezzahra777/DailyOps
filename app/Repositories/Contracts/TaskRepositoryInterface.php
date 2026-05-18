<?php

namespace App\Repositories\Contracts;

interface TaskRepositoryInterface
{
    public function getAll();

    public function findById($id);

    public function store(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function searchAndFilter($request);
}