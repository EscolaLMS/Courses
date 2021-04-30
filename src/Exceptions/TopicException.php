<?php

namespace EscolaLms\Courses\Exceptions;

use Exception;

class TopicException extends Exception
{
    const CONTENT_VALIDATION = 'Topic Content Validation fails';

    private array $data;

    public function __construct($message, $data)
    {
        $this->data = $data;
        parent::__construct($message);
    }

    public function getData()
    {
        return $this->data;
    }
}
