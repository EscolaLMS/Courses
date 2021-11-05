<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Core\Repositories\BaseRepository as BaseEscolaRepository;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository extends BaseEscolaRepository
{
    /**
     * Find model record for given id with relations
     *
     * @param int $id
     * @param array $columns
     * @param array $with relations
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function findWith(int $id, array $columns = ['*'], array $with = [], array $withCount = []): Model
    {
        $query = $this->model->newQuery()->with($with)->withCount($withCount);

        return $query->find($id, $columns);
    }
}
