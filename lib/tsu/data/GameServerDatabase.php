<?php

namespace tsu\data;

/**
 * Server database wrapper
 *
 * @version 1.0
 *
 * CHANGELOG:
 * - 1.0: Initial version
 */
class GameServerDatabase
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var array
     */
    private $config;

    public function __construct(PDO $connection, array $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    public function addServer($ip)
    {
        $this->query(
            sprintf(
                'INSERT INTO %s (ip) VALUES (?)',
                $this->config->table->server
            ),
            [$ip]
        );
    }

    public function deleteServer($id)
    {
        $this->query(
            sprintf(
                'DELETE FROM %s WHERE id = ?',
                $this->config->table->server
            ),
            [$id]
        );
    }

    /**
     * @return array
     */
    public function getServerIps()
    {
        return $this
            ->query(
                sprintf(
                    'SELECT ip FROM %s',
                    $this->config->table->server
                )
            )
            ->fetchAll();
    }

    /**
     * @param int
     * @return array
     */
    public function getStatus($serverId = -1)
    {
        //$config->
    }

    /**
     * @param string
     * @param array
     * @return PDOStatement
     */
    private function query($query, array $values = [])
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($values);

        return $statement;
    }
}

/*

Config:

database.dsn: game_monitor
database.table.server
database.table.status
database.auth.username
database.auth.password

update.interval
api.auth.token

*/
