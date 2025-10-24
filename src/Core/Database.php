<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 * 
 * Handles database connections using PDO with prepared statements.
 * Implements singleton pattern to ensure single connection instance.
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    
    private function __construct()
    {
        $this->connect();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                DB_OPTIONS
            );
            
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new \Exception('Database connection failed. Please check your configuration.');
        }
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return \PDOStatement
     */
    public function prepare(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Execute a query and return all results
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute a query and return single result
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Execute a query and return the last inserted ID
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return string
     */
    public function insert(string $sql, array $params = []): string
    {
        $this->prepare($sql, $params);
        return $this->getConnection()->lastInsertId();
    }
    
    /**
     * Execute an update/delete query and return affected rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return int
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin a database transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit a database transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback a database transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }
    
    /**
     * Check if currently in a transaction
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
