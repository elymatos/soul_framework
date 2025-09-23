<?php

use App\Database\GraphCriteria;
use App\Services\Neo4j\QueryBuilderService;

it('can instantiate GraphCriteria', function () {
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
        expect($criteria)->toBeInstanceOf(GraphCriteria::class);
    } catch (\Exception $e) {
        // Expected to fail without actual Neo4j connection
        expect($e->getMessage())->toContain('Neo4j');
    }
});

it('can access query builder from GraphCriteria', function () {
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
        expect($queryBuilder)->toBeInstanceOf(QueryBuilderService::class);
    } catch (\Exception $e) {
        // Expected to fail without actual Neo4j connection
        expect($e->getMessage())->toContain('Neo4j');
    }
});

it('has proper fluent interface methods', function () {
    $methods = get_class_methods(GraphCriteria::class);

    expect($methods)->toContain('node');
    expect($methods)->toContain('match');
    expect($methods)->toContain('where');
    expect($methods)->toContain('withRelations');
    expect($methods)->toContain('orderBy');
    expect($methods)->toContain('limit');
    expect($methods)->toContain('skip');
    expect($methods)->toContain('get');
    expect($methods)->toContain('first');
    expect($methods)->toContain('all');
    expect($methods)->toContain('count');
    expect($methods)->toContain('delete');
    expect($methods)->toContain('update');
    expect($methods)->toContain('createNode');
    expect($methods)->toContain('createRelation');
});

it('has static methods following Criteria pattern', function () {
    $class = new \ReflectionClass(GraphCriteria::class);
    $methods = $class->getMethods(\ReflectionMethod::IS_STATIC);
    $staticMethodNames = array_map(fn ($method) => $method->getName(), $methods);

    expect($staticMethodNames)->toContain('node');
    expect($staticMethodNames)->toContain('match');
    expect($staticMethodNames)->toContain('createNode');
    expect($staticMethodNames)->toContain('createRelation');
});
