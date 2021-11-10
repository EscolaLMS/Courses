<?php

namespace EscolaLms\Courses\Tests\Repositories\Mocks;

use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;

class CreateTopicApiRequestMock extends CreateTopicAPIRequest
{
    public function authorize()
    {
        return true;
    }

    public function validateResolved()
    {
        // do nothing
    }

    public function manualValidation(): self
    {
        $this->prepareForValidation();

        if (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        $instance = $this->getValidatorInstance();

        if ($instance->fails()) {
            $this->failedValidation($instance);
        }

        $this->passedValidation();

        return $this;
    }
}
