<?php

namespace gasemon;

interface ServerProcessorInterface
{
    public function isActive();
    public function process(array $servers);
}
