<?php

require_once 'vendor/autoload.php';
require_once 'lib/autoload.php';

use tsu\http\ApiTrait;

class App
{
    use ApiTrait;

    public function run()
    {
        $this->route();
    }

    protected function getTest(array $data)
    {
        $this->respond(200, $data);
    }

    protected function postTest(array $data)
    {
        $this->respond(202, $data);
    }

    protected function deleteTest(array $data)
    {
        $this->respond(205, $data);
    }

    protected function putTest(array $data)
    {
        $this->respond(201, $data);
    }
}

(new App())->run();
