<?php

namespace App\Core;

/**
 * Supabase Database Wrapper
 * 
 * Provides easy-to-use methods for database operations with Supabase.
 */
class SupabaseDatabase
{
    private $client;
    private $query;
    
    public function __construct()
    {
        $this->client = SupabaseClient::getInstance();
        // Get a fresh query client that will use the current user's access token
        $this->query = SupabaseClient::query();
    }
    
    /**
     * Set the Authorization header with the user's access token
     */
    private function setAuthHeader()
    {
        // Try to get the user's access token from session
        $user = Session::getUser();
        if ($user && isset($user['access_token']) && !empty($user['access_token'])) {
            // The query client is initialized with anon key, but we need to add user token
            // This is handled at the Supabase client level
            return $user['access_token'];
        }
        return null;
    }
    
    /**
     * Fetch a single record
     */
    public function fetchOne(string $table, array $filters = [])
    {
        try {
            $query = $this->query->from($table)->select('*');
            
            foreach ($filters as $column => $value) {
                $query = $query->eq($column, $value);
            }
            
            $result = $query->execute();
            
            if (isset($result->data) && is_array($result->data) && count($result->data) > 0) {
                return $result->data[0];
            }
            
            return null;
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase fetchOne error: ' . $e->getMessage());
            }
            return null;
        }
    }
    
    /**
     * Fetch multiple records
     */
    public function fetchAll(string $table, array $filters = [], string $orderBy = null, int $limit = null)
    {
        try {
            if (APP_DEBUG) {
                error_log("SupabaseDatabase: fetchAll called for table: $table");
                error_log("Filters: " . json_encode($filters));
            }
            
            $query = $this->query->from($table)->select('*');
            
            foreach ($filters as $column => $value) {
                if (APP_DEBUG) {
                    error_log("Adding filter: $column = $value");
                }
                $query = $query->eq($column, $value);
            }
            
            if ($orderBy) {
                if (APP_DEBUG) {
                    error_log("Ordering by: $orderBy (descending)");
                }
                // Order by specified column in descending order (most recent first)
                $query = $query->order($orderBy, ['ascending' => false]);
            }
            
            if ($limit) {
                if (APP_DEBUG) {
                    error_log("Limiting to: $limit");
                }
                $query = $query->limit($limit);
            }
            
            if (APP_DEBUG) {
                error_log("Executing query on table: $table");
            }
            
            try {
                $result = $query->execute();
                $data = $result->data ?? [];
                
                if (APP_DEBUG) {
                    error_log("Query returned " . count($data) . " records");
                }
                
                return $data;
            } catch (\Throwable $e) {
                // Handle any errors from the vendor library
                if (APP_DEBUG) {
                    error_log('Query execution failed: ' . $e->getMessage());
                }
                // Return empty array if query fails - this is expected if table doesn't exist
                return [];
            }
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase fetchAll error: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
            return [];
        }
    }
    
    /**
     * Insert a record
     */
    public function insert(string $table, array $data)
    {
        try {
            $result = $this->query->from($table)->insert($data)->execute();
            
            if (isset($result->data) && is_array($result->data) && count($result->data) > 0) {
                return $result->data[0];
            }
            
            return null;
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase insert error: ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Update records
     */
    public function update(string $table, array $filters, array $data)
    {
        try {
            $query = $this->query->from($table);
            
            foreach ($filters as $column => $value) {
                $query = $query->eq($column, $value);
            }
            
            $result = $query->update($data)->execute();
            
            return $result->data ?? [];
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase update error: ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Delete records
     */
    public function delete(string $table, array $filters)
    {
        try {
            $query = $this->query->from($table);
            
            foreach ($filters as $column => $value) {
                $query = $query->eq($column, $value);
            }
            
            $result = $query->delete()->execute();
            
            return true;
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase delete error: ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Count records
     */
    public function count(string $table, array $filters = [])
    {
        try {
            if (APP_DEBUG) {
                error_log("SupabaseDatabase: count called for table: $table");
                error_log("Filters: " . json_encode($filters));
            }
            
            $query = $this->query->from($table)->select('*', ['count' => 'exact']);
            
            foreach ($filters as $column => $value) {
                if (APP_DEBUG) {
                    error_log("Adding filter: $column = $value");
                }
                $query = $query->eq($column, $value);
            }
            
            try {
                $result = $query->execute();
                
                // The count is typically in the response headers or as a property
                $count = isset($result->count) ? $result->count : (isset($result->data) ? count($result->data) : 0);
                
                if (APP_DEBUG) {
                    error_log("Count result: $count");
                }
                
                return $count;
            } catch (\Throwable $e) {
                // Handle any errors from the vendor library
                if (APP_DEBUG) {
                    error_log('Count query execution failed: ' . $e->getMessage());
                }
                return 0;
            }
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase count error: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
            return 0;
        }
    }
    
    /**
     * Execute raw SQL query for UPDATE
     */
    public function executeUpdate(string $table, array $filters, array $data)
    {
        try {
            $query = $this->query->from($table);
            
            foreach ($filters as $column => $value) {
                $query = $query->eq($column, $value);
            }
            
            $result = $query->update($data)->execute();
            
            return $result->data ?? [];
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase executeUpdate error: ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Execute raw SQL query for DELETE
     */
    public function executeDelete(string $table, array $filters)
    {
        try {
            $query = $this->query->from($table);
            
            foreach ($filters as $column => $value) {
                $query = $query->eq($column, $value);
            }
            
            $result = $query->delete()->execute();
            
            return true;
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase executeDelete error: ' . $e->getMessage());
            }
            throw $e;
        }
    }
}

