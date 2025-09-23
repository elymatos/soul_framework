<?php 

$router->get('/', [
	'uses' => 'App\Http\Controllers\AppController@main',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('changeLanguage/{language}', [
	'uses' => 'App\Http\Controllers\AppController@changeLanguage',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('app/search', [
	'uses' => 'App\Http\Controllers\AppController@appSearchGet',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('app/search', [
	'uses' => 'App\Http\Controllers\AppController@appSearch',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('messages', [
	'uses' => 'App\Http\Controllers\AppController@messages',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('error', [
	'uses' => 'App\Http\Controllers\AppController@error',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report', [
	'uses' => 'App\Http\Controllers\AppController@report',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('grapher', [
	'uses' => 'App\Http\Controllers\AppController@grapher',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation', [
	'uses' => 'App\Http\Controllers\AppController@annotation',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('structure', [
	'uses' => 'App\Http\Controllers\AppController@structure',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('manager', [
	'uses' => 'App\Http\Controllers\AppController@manager',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('utils', [
	'uses' => 'App\Http\Controllers\AppController@utils',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cortical-network', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@index',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cortical-network/data', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@getData',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('cortical-network/column', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@createColumn',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('cortical-network/neuron', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@createNeuron',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('cortical-network/connection', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@createConnection',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('cortical-network/column/{id}', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@deleteColumn',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('cortical-network/neuron/{id}', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@deleteNeuron',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cortical-network/stats', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@getStats',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cortical-network/columns-list', [
	'uses' => 'App\Http\Controllers\CorticalNetworkController@getColumnsList',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('components/fesByFrame', [
	'uses' => 'App\Http\Controllers\ComponentsController@feCombobox',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('graph-editor', [
	'uses' => 'App\Http\Controllers\GraphEditorController@index',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('graph-editor/data', [
	'uses' => 'App\Http\Controllers\GraphEditorController@getData',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('graph-editor/node', [
	'uses' => 'App\Http\Controllers\GraphEditorController@saveNode',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('graph-editor/relation', [
	'uses' => 'App\Http\Controllers\GraphEditorController@saveRelation',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('graph-editor/delete-node', [
	'uses' => 'App\Http\Controllers\GraphEditorController@deleteNode',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('graph-editor/delete-edge', [
	'uses' => 'App\Http\Controllers\GraphEditorController@deleteEdge',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('graph-editor/save', [
	'uses' => 'App\Http\Controllers\GraphEditorController@saveGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('graph-editor/import', [
	'uses' => 'App\Http\Controllers\GraphEditorController@importGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('graph-editor/reset', [
	'uses' => 'App\Http\Controllers\GraphEditorController@resetGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('main/auth0Callback', [
	'uses' => 'App\Http\Controllers\LoginController@auth0Callback',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('auth0Login', [
	'uses' => 'App\Http\Controllers\LoginController@auth0Login',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('login', [
	'uses' => 'App\Http\Controllers\LoginController@login',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('twofactor', [
	'uses' => 'App\Http\Controllers\LoginController@twofactor',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('twofactor', [
	'uses' => 'App\Http\Controllers\LoginController@twofactorPost',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('login-error', [
	'uses' => 'App\Http\Controllers\LoginController@loginError',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('logout', [
	'uses' => 'App\Http\Controllers\LoginController@logout',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('impersonating', [
	'uses' => 'App\Http\Controllers\LoginController@impersonating',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('impersonating', [
	'uses' => 'App\Http\Controllers\LoginController@impersonatingPost',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sandbox/page1', [
	'uses' => 'App\Http\Controllers\SandboxController@page1',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sandbox/page2', [
	'uses' => 'App\Http\Controllers\SandboxController@page2',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('graph-viewer', [
	'uses' => 'App\Http\Controllers\GraphViewerController@main',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('graph-viewer/list', [
	'uses' => 'App\Http\Controllers\GraphViewerController@listGraphs',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('graph-viewer/view/{filename}', [
	'uses' => 'App\Http\Controllers\GraphViewerController@viewGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('graph-viewer/load/{filename}', [
	'uses' => 'App\Http\Controllers\GraphViewerController@loadGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('empty', [
	'uses' => 'App\Http\Controllers\Controller@empty',
	'as' => NULL,
	'middleware' => [],
	'where' => [],
	'domain' => NULL,
]);
