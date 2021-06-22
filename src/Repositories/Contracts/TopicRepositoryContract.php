<?php


namespace EscolaLms\Courses\Repositories\Contracts;


use EscolaLms\Courses\Models\Topic;

interface TopicRepositoryContract
{
    public function getById($id) : Topic;
}