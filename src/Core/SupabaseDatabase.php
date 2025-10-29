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
            // REST insert to avoid SDK inconsistencies
            $baseUrl = rtrim(SUPABASE_URL, '/');
            $url = $baseUrl . '/rest/v1/' . rawurlencode($table);

            $headers = [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . ($this->setAuthHeader() ?: SUPABASE_ANON_KEY),
                'Accept: application/json',
                'Content-Type: application/json',
                'Prefer: return=minimal'
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => json_encode([$data], JSON_UNESCAPED_SLASHES),
                    'ignore_errors' => true,
                    'timeout' => 10,
                ],
            ]);

            @file_get_contents($url, false, $context);
            return true;
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
            // REST update using PATCH with eq filters
            $baseUrl = rtrim(SUPABASE_URL, '/');
            $url = $baseUrl . '/rest/v1/' . rawurlencode($table);

            $queryParts = [];
            foreach ($filters as $column => $value) {
                $queryParts[] = rawurlencode($column) . '=eq.' . rawurlencode((string)$value);
            }
            if (!empty($queryParts)) {
                $url .= '?' . implode('&', $queryParts);
            }

            $headers = [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . ($this->setAuthHeader() ?: SUPABASE_ANON_KEY),
                'Accept: application/json',
                'Content-Type: application/json',
                'Prefer: return=minimal'
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'PATCH',
                    'header' => implode("\r\n", $headers),
                    'content' => json_encode($data, JSON_UNESCAPED_SLASHES),
                    'ignore_errors' => true,
                    'timeout' => 10,
                ],
            ]);

            @file_get_contents($url, false, $context);
            return true;
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
     * Count records via Supabase REST endpoint using HTTP headers (content-range)
     * This avoids issues in the PostgREST PHP client.
     */
    public function countRest(string $table, array $filters = []): int
    {
        try {
            $baseUrl = rtrim(SUPABASE_URL, '/');
            $url = $baseUrl . '/rest/v1/' . rawurlencode($table) . '?select=*';

            // Build filter query params (eq operator)
            $queryParts = [];
            foreach ($filters as $column => $value) {
                // Encode values safely; PostgREST expects 'eq.' prefix
                $queryParts[] = rawurlencode($column) . '=eq.' . rawurlencode((string)$value);
            }
            if (!empty($queryParts)) {
                $url .= '&' . implode('&', $queryParts);
            }

            // Use Range: 0-0 to minimize payload; Prefer: count=exact to get total
            $headers = [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . ($this->setAuthHeader() ?: SUPABASE_ANON_KEY),
                'Accept: application/json',
                'Prefer: count=exact',
                'Range: 0-0'
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers),
                    'ignore_errors' => true,
                    'timeout' => 10,
                ],
            ]);

            $result = @file_get_contents($url, false, $context);

            // Parse headers for content-range
            $count = null;
            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $hdr) {
                    if (stripos($hdr, 'content-range:') === 0) {
                        // Format: content-range: 0-0/123
                        $parts = explode('/', trim(substr($hdr, strpos($hdr, ':') + 1)));
                        if (count($parts) === 2) {
                            $total = trim($parts[1]);
                            if (is_numeric($total)) {
                                $count = (int)$total;
                                break;
                            }
                        }
                    }
                }
            }

            // Fallback: count decoded rows if header missing
            if ($count === null) {
                $data = @json_decode((string)$result, true);
                $count = is_array($data) ? count($data) : 0;
            }

            return $count ?? 0;
        } catch (\Throwable $e) {
            if (APP_DEBUG) {
                error_log('REST count error: ' . $e->getMessage());
            }
            return 0;
        }
    }

    /**
     * Fetch rows via Supabase REST with filters, ordering, pagination
     */
    public function fetchRest(
        string $table,
        array $filters = [],
        ?string $orderBy = null,
        bool $ascending = false,
        ?int $limit = null,
        ?int $offset = null,
        string $select = "*"
    ): array {
        try {
            $baseUrl = rtrim(SUPABASE_URL, '/');
            $url = $baseUrl . '/rest/v1/' . rawurlencode($table) . '?select=' . rawurlencode($select);

            // Filters
            $queryParts = [];
            foreach ($filters as $column => $value) {
                $queryParts[] = rawurlencode($column) . '=eq.' . rawurlencode((string)$value);
            }
            if (!empty($queryParts)) {
                $url .= '&' . implode('&', $queryParts);
            }

            // Order
            if ($orderBy) {
                $url .= '&order=' . rawurlencode($orderBy) . '.' . ($ascending ? 'asc' : 'desc');
            }

            // Range from offset/limit
            $rangeHeader = null;
            if ($limit !== null) {
                $start = max(0, (int)($offset ?? 0));
                $end = $start + max(0, (int)$limit) - 1;
                $rangeHeader = 'Range: ' . $start . '-' . $end;
            }

            $headers = [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . ($this->setAuthHeader() ?: SUPABASE_ANON_KEY),
                'Accept: application/json',
            ];
            if ($rangeHeader) {
                $headers[] = $rangeHeader;
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers),
                    'ignore_errors' => true,
                    'timeout' => 15,
                ],
            ]);

            $result = @file_get_contents($url, false, $context);
            $data = @json_decode((string)$result, true);

            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            if (APP_DEBUG) {
                error_log('REST fetch error: ' . $e->getMessage());
            }
            return [];
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
            // Use REST endpoint directly to avoid SDK differences where eq() may be unavailable on delete builders
            $baseUrl = rtrim(SUPABASE_URL, '/');
            $url = $baseUrl . '/rest/v1/' . rawurlencode($table);

            // Build filter query params using eq operator
            $queryParts = [];
            foreach ($filters as $column => $value) {
                $queryParts[] = rawurlencode($column) . '=eq.' . rawurlencode((string)$value);
            }
            if (!empty($queryParts)) {
                $url .= '?' . implode('&', $queryParts);
            }

            $headers = [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . ($this->setAuthHeader() ?: SUPABASE_ANON_KEY),
                'Accept: application/json',
                'Prefer: return=minimal'
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'DELETE',
                    'header' => implode("\r\n", $headers),
                    'ignore_errors' => true,
                    'timeout' => 10,
                ],
            ]);

            @file_get_contents($url, false, $context);
            // If the request didn't throw, treat as success (Supabase usually returns 204 No Content)
            return true;
        } catch (\Exception $e) {
            if (APP_DEBUG) {
                error_log('Supabase executeDelete error: ' . $e->getMessage());
            }
            throw $e;
        }
    }
}

