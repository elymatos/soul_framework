<?php

use App\Services\Neo4j\QueryBuilderService;

it('can build basic node queries', function () {
    $builder = new QueryBuilderService;

    $query = $builder
        ->match('(n:Neuron)')
        ->where('n.layer = 4')
        ->returnClause('n')
        ->build();

    expect($query)->toBe('MATCH (n:Neuron) WHERE n.layer = 4 RETURN n');
});

it('can build node creation queries', function () {
    $builder = new QueryBuilderService;

    $query = $builder
        ->create('(n:Neuron {name: "Test", layer: 4})')
        ->returnClause('n')
        ->build();

    expect($query)->toBe('CREATE (n:Neuron {name: "Test", layer: 4}) RETURN n');
});

it('can build queries with parameters', function () {
    $builder = new QueryBuilderService;

    $paramKey = $builder->addParameter('Test Neuron');
    $query = $builder
        ->match('(n:Neuron)')
        ->whereParameter('n.name', '=', 'Test Neuron')
        ->returnClause('n')
        ->build();

    $parameters = $builder->getParameters();

    expect($query)->toContain('WHERE n.name = $');
    expect($parameters)->toHaveCount(2); // One from whereParameter, one manually added
});

it('can build relationship queries', function () {
    $builder = new QueryBuilderService;

    $query = $builder
        ->match('(a:Neuron)-[r:CONNECTS_TO]->(b:Neuron)')
        ->where('a.layer = 4')
        ->returnClause('a, r, b')
        ->build();

    expect($query)->toBe('MATCH (a:Neuron)-[r:CONNECTS_TO]->(b:Neuron) WHERE a.layer = 4 RETURN a, r, b');
});

it('can build complex queries with ordering and limits', function () {
    $builder = new QueryBuilderService;

    $query = $builder
        ->match('(n:Neuron)')
        ->where('n.layer = 4')
        ->returnClause('n')
        ->orderBy('n.name', 'ASC')
        ->limit(10)
        ->build();

    expect($query)->toBe('MATCH (n:Neuron) WHERE n.layer = 4 RETURN n ORDER BY n.name ASC LIMIT 10');
});

it('can create node patterns using helper method', function () {
    $pattern = QueryBuilderService::nodePattern('n', 'Neuron', ['layer' => 4, 'name' => 'Test']);

    expect($pattern)->toBe('(n:Neuron {layer: 4, name: \'Test\'})');
});

it('can create relationship patterns using helper method', function () {
    $pattern = QueryBuilderService::relationshipPattern('CONNECTS_TO', 'r', ['strength' => 0.8]);

    expect($pattern)->toBe('[r:CONNECTS_TO {strength: 0.8}]');
});

it('can build update queries', function () {
    $builder = new QueryBuilderService;

    $query = $builder
        ->match('(n:Neuron)')
        ->where('ID(n) = 123')
        ->set('n.activation_level = 0.7, n.updated_at = timestamp()')
        ->returnClause('n')
        ->build();

    expect($query)->toBe('MATCH (n:Neuron) WHERE ID(n) = 123 SET n.activation_level = 0.7, n.updated_at = timestamp() RETURN n');
});

it('can build delete queries', function () {
    $builder = new QueryBuilderService;

    $query = $builder
        ->match('(n:Neuron)')
        ->where('n.layer = 4')
        ->delete('n')
        ->build();

    expect($query)->toBe('MATCH (n:Neuron) WHERE n.layer = 4 DELETE n');
});

it('can build merge queries', function () {
    $builder = new QueryBuilderService;

    $query = $builder
        ->merge('(n:Neuron {id: "unique-id"})')
        ->set('n.layer = 4, n.name = "Test"')
        ->returnClause('n')
        ->build();

    expect($query)->toBe('MERGE (n:Neuron {id: "unique-id"}) SET n.layer = 4, n.name = "Test" RETURN n');
});
