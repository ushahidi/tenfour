<?php

class ApiController extends BaseController {

    protected $statusCode = 200;

    /**
    * @return mixed
    */

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
    * @param mixed $statusCode
    * @return $this
    */

    public function setStatusCode()
    {
        $this->statusCode = $statusCode;

        return $this;

    }

    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode, $headers);
    }

    public function respondWithError($message)
    {
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    public function respondCreated($message)
    {
        return $this->setStatusCode(201)->respond([
            'message' => $message
        ]);
    }

}
