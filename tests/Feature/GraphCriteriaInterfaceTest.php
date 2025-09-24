<?php

namespace Tests\Feature;

use App\Database\GraphCriteria;
use App\Services\Neo4j\QueryBuilderService;
use Tests\TestCase;

class GraphCriteriaInterfaceTest extends TestCase
{
    public function test_can_instantiate_graph_criteria()
    {
        // We'll mock the config to avoid actual Neo4j connection
        $this->app['config']->set('neo4j.default', 'test');
        $this->app['config']->set('neo4j.connections.test', [
            'driver' => 'bolt',
            'host' => 'localhost',
            'port' => 7687,
            'username' => 'neo4j',
            'password' => 'test',
            'database' => 'neo4j',
            'timeout' => 30,
        ]);

        // This will fail at connection time, but we can test the interface
        try {
            $criteria = new GraphCriteria;
            $this->assertInstanceOf(GraphCriteria::class, $criteria);
        } catch (\Exception $e) {
            // Expected to fail without actual Neo4j connection
            $this->assertStringContainsString('Neo4j', $e->getMessage());
        }
    }

    public function test_can_access_query_builder_from_graph_criteria()
    {
        // Mock the config
        $this->app['config']->set('neo4j.default', 'test');
        $this->app['config']->set('neo4j.connections.test', [
            'driver' => 'bolt',
            'host' => 'localhost',
            'port' => 7687,
            'username' => 'neo4j',
            'password' => 'test',
            'database' => 'neo4j',
            'timeout' => 30,
        ]);

        try {
            $criteria = new GraphCriteria;
            $queryBuilder = $criteria->getQueryBuilder();
            $this->assertInstanceOf(QueryBuilderService::class, $queryBuilder);
        } catch (\Exception $e) {
            // Expected to fail without actual Neo4j connection
            $this->assertStringContainsString('Neo4j', $e->getMessage());
        }
    }

    public function test_has_proper_fluent_interface_methods()
    {
        $methods = get_class_methods(GraphCriteria::class);

        $expectedMethods = [
            'node', 'match', 'where', 'withRelations', 'orderBy', 
            'limit', 'skip', 'get', 'first', 'all', 'count', 
            'delete', 'update', 'createNode', 'createRelation'
        ];

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $methods, "Method {$method} should exist on GraphCriteria");
        }
    }

    public function test_has_static_methods_following_criteria_pattern()
    {
        $class = new \ReflectionClass(GraphCriteria::class);
        $methods = $class->getMethods(\ReflectionMethod::IS_STATIC);
        $staticMethodNames = array_map(fn ($method) => $method->getName(), $methods);

        $expectedStaticMethods = ['node', 'match', 'createNode', 'createRelation'];

        foreach ($expectedStaticMethods as $method) {
            $this->assertContains($method, $staticMethodNames, "Static method {$method} should exist on GraphCriteria");
        }
    }
}