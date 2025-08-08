<?php

namespace App\Soul\Models;

use Illuminate\Support\Collection;

/**
 * BaseGraphModel - Base class for all SOUL graph models
 *
 * Provides common functionality for graph-based models including
 * property management, validation, and Neo4j integration support.
 */
abstract class BaseGraphModel
{
    protected string $label;
    protected array $fillable = [];
    protected array $casts = [];
    protected array $attributes = [];
    protected array $original = [];
    
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }
    
    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Set an attribute value
     */
    public function setAttribute(string $key, $value): void
    {
        if (isset($this->casts[$key])) {
            $value = $this->castAttribute($key, $value);
        }
        
        $this->attributes[$key] = $value;
    }
    
    /**
     * Get an attribute value
     */
    public function getAttribute(string $key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }
        
        $value = $this->attributes[$key];
        
        if (isset($this->casts[$key])) {
            return $this->castAttribute($key, $value);
        }
        
        return $value;
    }
    
    /**
     * Cast an attribute to the appropriate type
     */
    protected function castAttribute(string $key, $value)
    {
        $castType = $this->casts[$key];
        
        if (is_null($value)) {
            return $value;
        }
        
        switch ($castType) {
            case 'boolean':
            case 'bool':
                return (bool) $value;
            case 'integer':
            case 'int':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return is_array($value) ? $value : json_decode($value, true) ?? [];
            case 'collection':
                return collect(is_array($value) ? $value : json_decode($value, true) ?? []);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Get the model's label for Neo4j
     */
    public function getLabel(): string
    {
        return $this->label;
    }
    
    /**
     * Get all attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Get fillable attributes
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }
    
    /**
     * Get the model as an array suitable for Neo4j
     */
    public function toNeo4jArray(): array
    {
        $result = [];
        
        foreach ($this->attributes as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $result[$key] = json_encode($value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Create a new model instance from Neo4j node data
     */
    public static function fromNeo4j(array $nodeData): static
    {
        $instance = new static();
        
        foreach ($nodeData as $key => $value) {
            // Attempt to decode JSON strings back to arrays
            if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }
            
            $instance->setAttribute($key, $value);
        }
        
        return $instance;
    }
    
    /**
     * Magic getter for attributes
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter for attributes
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Magic isset check
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Convert to array
     */
    public function toArray(): array
    {
        $result = [];
        
        foreach ($this->attributes as $key => $value) {
            $result[$key] = $this->getAttribute($key);
        }
        
        return $result;
    }
    
    /**
     * Convert to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}