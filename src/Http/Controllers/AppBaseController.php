<?php

namespace EscolaLms\Courses\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;

/**
 * SWAGGER_VERSION
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends EscolaLmsBaseController
{
    public function sendDataError($error, $data, $code = 422)
    {
        return $this->sendResponse($data, $error, $code);
    }
}
