<?php

namespace tsu\game;

use tsu\data;

class ServerMonitor
{
    protected $serverDatabase;
    protected $gameQ;

    public function __construct(GameServerDatabase $database)
    {
        $this->database = $database;
        $this->gameQ = new GameQ();

        foreach ($this->database->getServers() as $server) {
            $this->gameQ->addServer($server);
        }
    }

    public function updateServers()
    {
        //
    }

}
