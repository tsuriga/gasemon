<?php

namespace gasemon;

class ServerMonitor
{
    private $db;
    private $gameType;

    private $gameQ;

    /**
     * @param \SQLite3 $db
     * @param ServerProcessorInterface $processor
     * @param string $gameType
     */
    public function __construct(
        \SQLite3 $db,
        ServerProcessorInterface $processor,
        $gameType
    ) {
        $this->db = $db;
        $this->processor = $processor;
        $this->gameType = $gameType;

        $this->gameQ = new \GameQ();
    }

    private function loadServers()
    {
        $this->gameQ->clearServers();

        $servers = $this->db->query('SELECT addr FROM server');

        while ($server = $servers->fetchArray(SQLITE3_ASSOC)) {
            $this->gameQ->addServer([
                'type' => $this->gameType,
                'host' => $server['addr']
            ]);
        }
    }

    public function runCheck()
    {
        if (!$this->processor->isActive()) {
            return;
        }

        $this->loadServers();

        $serverInfo = $this->gameQ->requestData();

        $this->processor->process($serverInfo);
    }
}
