<?php

namespace tsu\http;

trait ApiTrait
{
    public function route()
    {
        $httpMethod = strtolower(@$_SERVER['REQUEST_METHOD']);
        $action = ucfirst(
            explode(
                '/',
                str_replace(
                    dirname(@$_SERVER['SCRIPT_NAME']) . '/',
                    '',
                    @$_SERVER['REDIRECT_URL']
                ),
                2
            )[0]
        );

        $method = $httpMethod . $action;

        if (method_exists($this, $method)) {
            $parameters = [];

            if ($httpMethod === 'get') {
                $parameters = $_GET;
            } else {
                parse_str(file_get_contents('php://input'), $parameters);
            }

            $this->$method($parameters);
        } else {
            $route = strtoupper($httpMethod) . '/' . lcfirst($action);

            throw new BadMethodCallException(
                'Route ' . $route . ' not supported'
            );
        }
    }

    public function respond($code, array $data)
    {
        header('Content-type: application/json', false, $code);

        echo json_encode($data);
    }

    public function authenticate($signature, array $data)
    {
        //
        header('HTTP/1.1 401 Unauthorized', false, 401);
    }
}
