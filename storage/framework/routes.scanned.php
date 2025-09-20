<?php 

$router->get('dataset/{id}/projects', [
	'uses' => 'App\Http\Controllers\Dataset\ProjectController@projects',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/{id}/projects/formNew', [
	'uses' => 'App\Http\Controllers\Dataset\ProjectController@projectsFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/{id}/projects/grid', [
	'uses' => 'App\Http\Controllers\Dataset\ProjectController@projectsGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('dataset/{id}/projects/new', [
	'uses' => 'App\Http\Controllers\Dataset\ProjectController@projectsNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('dataset/{id}/projects/{idProject}', [
	'uses' => 'App\Http\Controllers\Dataset\ProjectController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/{id}/corpus', [
	'uses' => 'App\Http\Controllers\Dataset\CorpusController@corpus',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/{id}/corpus/formNew', [
	'uses' => 'App\Http\Controllers\Dataset\CorpusController@corpusFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/{id}/corpus/grid', [
	'uses' => 'App\Http\Controllers\Dataset\CorpusController@corpusGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('dataset/{id}/corpus/new', [
	'uses' => 'App\Http\Controllers\Dataset\CorpusController@corpusNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('dataset/{id}/corpus/{idCorpus}', [
	'uses' => 'App\Http\Controllers\Dataset\CorpusController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/new', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('dataset/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/{id}/edit', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dataset/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('dataset', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('dataset/new', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('dataset/{id}', [
	'uses' => 'App\Http\Controllers\Dataset\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/{id}/document', [
	'uses' => 'App\Http\Controllers\Video\DocumentController@corpus',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/{id}/document/formNew', [
	'uses' => 'App\Http\Controllers\Video\DocumentController@documentFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/{id}/document/grid', [
	'uses' => 'App\Http\Controllers\Video\DocumentController@documentGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('video/{id}/document/new', [
	'uses' => 'App\Http\Controllers\Video\DocumentController@documentNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('video/{id}/document/{idDocument}', [
	'uses' => 'App\Http\Controllers\Video\DocumentController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('video/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/{id}/edit', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('video', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/{id}/formUpload', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@formUpload',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('video/upload', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@upload',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/new', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('video/new', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('video/{id}', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('video/listForSelect', [
	'uses' => 'App\Http\Controllers\Video\ResourceController@listForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/users', [
	'uses' => 'App\Http\Controllers\Project\UserController@users',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/users/formNew', [
	'uses' => 'App\Http\Controllers\Project\UserController@usersFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/users/grid', [
	'uses' => 'App\Http\Controllers\Project\UserController@usersGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('project/{id}/users/new', [
	'uses' => 'App\Http\Controllers\Project\UserController@projectsNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('project/{id}/users/{idUser}', [
	'uses' => 'App\Http\Controllers\Project\UserController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/datasets', [
	'uses' => 'App\Http\Controllers\Project\DatasetController@datasets',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/datasets/formNew', [
	'uses' => 'App\Http\Controllers\Project\DatasetController@datasetsFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/datasets/grid', [
	'uses' => 'App\Http\Controllers\Project\DatasetController@datasetsGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('project/datasets/new', [
	'uses' => 'App\Http\Controllers\Project\DatasetController@datasetsNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('project/{idProject}/datasets/{idDataset}', [
	'uses' => 'App\Http\Controllers\Project\DatasetController@datasetsDelete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('project/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/data', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@data',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/new', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/edit', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('project/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('project', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('project/new', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('project/{id}', [
	'uses' => 'App\Http\Controllers\Project\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('domain', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('domain/new', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('domain/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('domain/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('domain/{id}/edit', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('domain/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('domain', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('domain/new', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('domain/{id}', [
	'uses' => 'App\Http\Controllers\Domain\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('domain/{id}/entries', [
	'uses' => 'App\Http\Controllers\Domain\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/deixis', [
	'uses' => 'App\Http\Controllers\Annotation\DeixisController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/deixis/{idDocument}/{idDynamicObject?}', [
	'uses' => 'App\Http\Controllers\Annotation\DeixisController@annotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/deixis/{idDocument}/{idDynamicObject}', [
	'uses' => 'App\Http\Controllers\Annotation\DeixisController@deleteObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/deixis/deleteAllBBoxes/{idDocument}/{idDynamicObject}', [
	'uses' => 'App\Http\Controllers\Annotation\DeixisController@deleteAllBBoxes',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/session/script/{folder}', [
	'uses' => 'App\Http\Controllers\Annotation\SessionController@jsObjects',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/session/start', [
	'uses' => 'App\Http\Controllers\Annotation\SessionController@sessionStart',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/session/end', [
	'uses' => 'App\Http\Controllers\Annotation\SessionController@sessionEnd',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/video/script/{folder}', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@jsObjects',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/video/object', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@getObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/video/object/search', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@objectSearch',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/video/createNewObjectAtLayer', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@createNewObjectAtLayer',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/video/cloneObject', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@cloneObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/video/updateObjectAnnotation', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@updateObjectAnnotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/video/updateObjectRange', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@updateObjectRange',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/video/getBBox', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@getBBox',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/video/createBBox', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@createBBox',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/video/updateBBox', [
	'uses' => 'App\Http\Controllers\Annotation\VideoController@updateBBox',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/fe', [
	'uses' => 'App\Http\Controllers\Annotation\FEController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/fe/sentence/{idDocumentSentence}/{idAnnotationSet?}', [
	'uses' => 'App\Http\Controllers\Annotation\FEController@annotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/fe/asExternal/{idAS}', [
	'uses' => 'App\Http\Controllers\Annotation\FEController@annotationSet',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/fe/asExternal/{idAnnotationSet}', [
	'uses' => 'App\Http\Controllers\Annotation\FEController@deleteAS',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/browse/searchSentence', [
	'uses' => 'App\Http\Controllers\Annotation\BrowseController@search',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/browse/searchDocument/{taskGroupName}', [
	'uses' => 'App\Http\Controllers\Annotation\BrowseController@searchDocument',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/staticEvent', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/staticEvent/grid', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@grid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/staticEvent/grid/{idDocument}/sentences', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@documentSentences',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/staticEvent/sentence/{idDocumentSentence}', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@annotationSentence',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/staticEvent/addFrame', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@annotationSentenceFes',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('annotation/staticEvent/fes/{idDocumentSentence}/{idFrame}', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@annotationSentenceFesSubmit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/staticEvent/fes/{idDocumentSentence}/{idFrame}', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@annotationSentenceFesDelete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/staticEvent/comment', [
	'uses' => 'App\Http\Controllers\Annotation\StaticEventController@annotationComment',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/corpus/script/{folder}', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@jsObjects',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/corpus/as/{corpusAnnotationType}/{idAS}/{token?}', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@annotationSet',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/corpus/lus/{corpusAnnotationType}/{idDocumentSentence}/{idWord}', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@getLUs',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/corpus/annotationset/{idAnnotationSet}/{corpusAnnotationType}', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@deleteAS',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/corpus/createAS', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@createAS',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/corpus/object', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@annotate',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/corpus/object', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@deleteObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/corpus/annotationset/{idAnnotationSet}/change/{idLU}/{corpusAnnotationType}', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@changeAnnotationSet',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/corpus/lome/accepted', [
	'uses' => 'App\Http\Controllers\Annotation\CorpusController@lomeAccepted',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/as', [
	'uses' => 'App\Http\Controllers\Annotation\AnnotationSetController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/as/sentence/{idDocumentSentence}/{idAnnotationSet?}', [
	'uses' => 'App\Http\Controllers\Annotation\AnnotationSetController@annotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/staticBBox', [
	'uses' => 'App\Http\Controllers\Annotation\StaticBBoxController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/staticBBox/sentences/{idDocument}', [
	'uses' => 'App\Http\Controllers\Annotation\StaticBBoxController@gridSentences',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/staticBBox/{idDocument}/{idStaticObject?}', [
	'uses' => 'App\Http\Controllers\Annotation\StaticBBoxController@annotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/staticBBox/{idDocument}/{idStaticObject}', [
	'uses' => 'App\Http\Controllers\Annotation\StaticBBoxController@deleteObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/comment/update', [
	'uses' => 'App\Http\Controllers\Annotation\CommentController@updateComment',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/comment/{idAnnotationComment}', [
	'uses' => 'App\Http\Controllers\Annotation\CommentController@deleteComment',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/dynamicMode', [
	'uses' => 'App\Http\Controllers\Annotation\DynamicModeController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/dynamicMode/sentences/{idDocument}', [
	'uses' => 'App\Http\Controllers\Annotation\DynamicModeController@gridSentences',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/dynamicMode/{idDocument}/{idDynamicObject?}', [
	'uses' => 'App\Http\Controllers\Annotation\DynamicModeController@annotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/dynamicMode/{idDocument}/{idDynamicObject}', [
	'uses' => 'App\Http\Controllers\Annotation\DynamicModeController@deleteObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('annotation/dynamicMode/deleteAllBBoxes/{idDocument}/{idDynamicObject}', [
	'uses' => 'App\Http\Controllers\Annotation\DynamicModeController@deleteAllBBoxes',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/fullText', [
	'uses' => 'App\Http\Controllers\Annotation\FullTextController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/fullText/sentence/{idDocumentSentence}/{idAnnotationSet?}', [
	'uses' => 'App\Http\Controllers\Annotation\FullTextController@annotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/image/script/{folder}', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@jsObjects',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/image/object', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@getObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/image/object/search', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@objectSearch',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/image/createNewObjectAtLayer', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@createNewObjectAtLayer',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/image/cloneObject', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@cloneObject',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/image/updateObjectAnnotation', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@updateObjectAnnotation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/image/updateObjectRange', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@updateObjectRange',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('annotation/image/getBBox', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@getBBox',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/image/createBBox', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@createBBox',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('annotation/image/updateBBox', [
	'uses' => 'App\Http\Controllers\Annotation\ImageController@updateBBox',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('entry', [
	'uses' => 'App\Http\Controllers\Entry\EntryController@entry',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('corpus', [
	'uses' => 'App\Http\Controllers\Corpus\BrowseController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('corpus/browse/search', [
	'uses' => 'App\Http\Controllers\Corpus\BrowseController@search',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('corpus/{id}/edit', [
	'uses' => 'App\Http\Controllers\Corpus\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('corpus/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\Corpus\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('corpus', [
	'uses' => 'App\Http\Controllers\Corpus\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('corpus/new', [
	'uses' => 'App\Http\Controllers\Corpus\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('corpus/new', [
	'uses' => 'App\Http\Controllers\Corpus\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('corpus/{id}', [
	'uses' => 'App\Http\Controllers\Corpus\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('corpus/listForSelect', [
	'uses' => 'App\Http\Controllers\Corpus\ResourceController@listForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('corpus/{id}/entries', [
	'uses' => 'App\Http\Controllers\Corpus\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('document/{id}/edit', [
	'uses' => 'App\Http\Controllers\Document\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('document/{id}/formCorpus', [
	'uses' => 'App\Http\Controllers\Document\ResourceController@formCorpus',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('document', [
	'uses' => 'App\Http\Controllers\Document\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('document/new', [
	'uses' => 'App\Http\Controllers\Document\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('document/new', [
	'uses' => 'App\Http\Controllers\Document\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('document/{id}', [
	'uses' => 'App\Http\Controllers\Document\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('document/listForSelect', [
	'uses' => 'App\Http\Controllers\Document\ResourceController@listForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('document/{id}/entries', [
	'uses' => 'App\Http\Controllers\Document\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/semanticTypes', [
	'uses' => 'App\Http\Controllers\FE\SemanticTypeController@semanticTypes',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/relations/{idEntityRelation}/frame/{idFrameBase}', [
	'uses' => 'App\Http\Controllers\FE\RelationController@relations',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/relations/{idEntityRelation}/formNew', [
	'uses' => 'App\Http\Controllers\FE\RelationController@relationsFEFormNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/relations/{idEntityRelation}/grid', [
	'uses' => 'App\Http\Controllers\FE\RelationController@gridRelationsFE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/constraints', [
	'uses' => 'App\Http\Controllers\FE\ConstraintController@constraints',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/constraints/formNew', [
	'uses' => 'App\Http\Controllers\FE\ConstraintController@constraintsFormNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/constraints/grid', [
	'uses' => 'App\Http\Controllers\FE\ConstraintController@constraintsGrid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('fe', [
	'uses' => 'App\Http\Controllers\FE\ResourceController@newFE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/edit', [
	'uses' => 'App\Http\Controllers\FE\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/main', [
	'uses' => 'App\Http\Controllers\FE\ResourceController@main',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('fe/{id}', [
	'uses' => 'App\Http\Controllers\FE\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\FE\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('fe/{id}', [
	'uses' => 'App\Http\Controllers\FE\ResourceController@update',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('fe/{id}/entries', [
	'uses' => 'App\Http\Controllers\FE\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/relations', [
	'uses' => 'App\Http\Controllers\Construction\RelationController@relations',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/relations/formNew', [
	'uses' => 'App\Http\Controllers\Construction\RelationController@formNewRelation',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/relations/grid', [
	'uses' => 'App\Http\Controllers\Construction\RelationController@gridRelation',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('report/cxn/grid', [
	'uses' => 'App\Http\Controllers\Construction\ReportController@grid',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/cxn/data', [
	'uses' => 'App\Http\Controllers\Construction\ReportController@data',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/cxn/{idConstruction?}/{view?}', [
	'uses' => 'App\Http\Controllers\Construction\ReportController@report',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('construction/list/forSelect', [
	'uses' => 'App\Http\Controllers\Construction\ReportController@listForSelect',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/constraints', [
	'uses' => 'App\Http\Controllers\Construction\ConstraintController@constraints',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/constraints/formNew', [
	'uses' => 'App\Http\Controllers\Construction\ConstraintController@constraintsFormNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/constraints/grid', [
	'uses' => 'App\Http\Controllers\Construction\ConstraintController@constraintsGrid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn', [
	'uses' => 'App\Http\Controllers\Construction\BrowseController@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('cxn/grid', [
	'uses' => 'App\Http\Controllers\Construction\BrowseController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/ces', [
	'uses' => 'App\Http\Controllers\Construction\CEController@ces',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/ces/formNew', [
	'uses' => 'App\Http\Controllers\Construction\CEController@formNewCE',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/ces/grid', [
	'uses' => 'App\Http\Controllers\Construction\CEController@gridCE',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/new', [
	'uses' => 'App\Http\Controllers\Construction\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('cxn', [
	'uses' => 'App\Http\Controllers\Construction\ResourceController@store',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('cxn/{idcxn}', [
	'uses' => 'App\Http\Controllers\Construction\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}', [
	'uses' => 'App\Http\Controllers\Construction\ResourceController@get',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('cxn/{id}/entries', [
	'uses' => 'App\Http\Controllers\Construction\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
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

$router->get('layers', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('layers/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/layergroup/new', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@formNewLayerGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/layergroup/{idLayerGroup}/edit', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@layergroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/layergroup/{idLayerGroup}/formEdit', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@formEditLayerGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('layers/layergroup/new', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@newLayerGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('layers/layergroup', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@updateLayerGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('layers/layergroup/{idLayerGroup}', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@deleteLayerGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/layertype/new', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@formNewLayerType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/layertype/{idLayerType}/edit', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@layertype',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/layertype/{idLayerType}/formEdit', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@formEditLayerType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('layers/layertype/new', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@newLayerType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('layers/layertype', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@updateLayerType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('layers/layertype/{idLayerType}', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@deleteLayerType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/genericlabel/new', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@formNewGenericLabel',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/genericlabel/{idGenericLabel}/edit', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@genericlabel',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/genericlabel/{idGenericLabel}/formEdit', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@formEditGenericLabel',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('layers/genericlabel/new', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@newGenericLabel',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('layers/genericlabel', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@updateGenericLabel',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('layers/genericlabel/{idGenericLabel}', [
	'uses' => 'App\Http\Controllers\Layers\ResourceController@deleteGenericLabel',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('layers/layertype/{id}/entries', [
	'uses' => 'App\Http\Controllers\Layers\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('network', [
	'uses' => 'App\Http\Controllers\Network\BrowseController@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('network/grid', [
	'uses' => 'App\Http\Controllers\Network\BrowseController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('network/listForTree', [
	'uses' => 'App\Http\Controllers\Network\BrowseController@listForTree',
	'as' => NULL,
	'middleware' => ['master'],
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

$router->get('usertask/{id}/edit', [
	'uses' => 'App\Http\Controllers\Task\UserTaskController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('usertask/{id}/documents', [
	'uses' => 'App\Http\Controllers\Task\UserTaskController@documents',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('usertask/{id}/documents/formNew', [
	'uses' => 'App\Http\Controllers\Task\UserTaskController@documentsNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('usertask/{id}/documents/grid', [
	'uses' => 'App\Http\Controllers\Task\UserTaskController@documentsGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('usertask/documents/new', [
	'uses' => 'App\Http\Controllers\Task\UserTaskController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('usertask/{idUserTask}', [
	'uses' => 'App\Http\Controllers\Task\UserTaskController@deleteUserTask',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('usertask/{idUserTask}/documents/{idUserTaskDocument}', [
	'uses' => 'App\Http\Controllers\Task\UserTaskController@deleteUserTaskDocument',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task/{id}/users', [
	'uses' => 'App\Http\Controllers\Task\UserController@users',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task/{id}/users/formNew', [
	'uses' => 'App\Http\Controllers\Task\UserController@usersFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task/{id}/users/grid', [
	'uses' => 'App\Http\Controllers\Task\UserController@usersGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('task/{id}/users/new', [
	'uses' => 'App\Http\Controllers\Task\UserController@projectsNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('task/{id}/users/{idUserTask}', [
	'uses' => 'App\Http\Controllers\Task\UserController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task/new', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('task/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task/{id}/edit', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('task/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('task', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('task/new', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('task/{id}', [
	'uses' => 'App\Http\Controllers\Task\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/document', [
	'uses' => 'App\Http\Controllers\Image\DocumentController@document',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/document/formNew', [
	'uses' => 'App\Http\Controllers\Image\DocumentController@documentFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/document/grid', [
	'uses' => 'App\Http\Controllers\Image\DocumentController@documentGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('image/{id}/document/new', [
	'uses' => 'App\Http\Controllers\Image\DocumentController@documentNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('image/{id}/document/{idDocument}', [
	'uses' => 'App\Http\Controllers\Image\DocumentController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/dataset', [
	'uses' => 'App\Http\Controllers\Image\DatasetController@dataset',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/dataset/formNew', [
	'uses' => 'App\Http\Controllers\Image\DatasetController@datasetFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/dataset/grid', [
	'uses' => 'App\Http\Controllers\Image\DatasetController@datasetGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('image/{id}/dataset/new', [
	'uses' => 'App\Http\Controllers\Image\DatasetController@datasetNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('image/{id}/dataset/{idDataset}', [
	'uses' => 'App\Http\Controllers\Image\DatasetController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('image/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/data', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@data',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/edit', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/{id}/editForm', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@editForm',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('image', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/new', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('image/new', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('image/{id}', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('image/listForSelect', [
	'uses' => 'App\Http\Controllers\Image\ResourceController@listForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/qualia', [
	'uses' => 'App\Http\Controllers\LU\QualiaController@qualia',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/qualia/formNew/{fragment?}', [
	'uses' => 'App\Http\Controllers\LU\QualiaController@qualiaFormNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/qualia/grid', [
	'uses' => 'App\Http\Controllers\LU\QualiaController@qualiaGrid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lu/qualia/{id}', [
	'uses' => 'App\Http\Controllers\LU\QualiaController@qualiaLU',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lu/qualia/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\LU\QualiaController@deleteQualiaRelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/aiSuggestion', [
	'uses' => 'App\Http\Controllers\LU\AISuggestionController@aiSuggestion',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lu/aiSuggestion', [
	'uses' => 'App\Http\Controllers\LU\AISuggestionController@getAiSuggestions',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/semanticTypes', [
	'uses' => 'App\Http\Controllers\LU\SemanticTypeController@semanticTypes',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('reframing', [
	'uses' => 'App\Http\Controllers\LU\ReframingController@report',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('reframing/search', [
	'uses' => 'App\Http\Controllers\LU\ReframingController@search',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('reframing/lu/{idLU}', [
	'uses' => 'App\Http\Controllers\LU\ReframingController@reframingLU',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('reframing/edit/{idLU}/{idNewFrame}', [
	'uses' => 'App\Http\Controllers\LU\ReframingController@reframingEdit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('reframing/fes/{idLU}/{idNewFrame}', [
	'uses' => 'App\Http\Controllers\LU\ReframingController@reframingFEs',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('reframing', [
	'uses' => 'App\Http\Controllers\LU\ReframingController@update',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/script/{file}', [
	'uses' => 'App\Http\Controllers\LU\ReportController@scripts',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('report/lu/search', [
	'uses' => 'App\Http\Controllers\LU\ReportController@search',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/{idLU}/textual', [
	'uses' => 'App\Http\Controllers\LU\ReportController@reportTextual',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/{idLU}/static', [
	'uses' => 'App\Http\Controllers\LU\ReportController@reportStatic',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/static/object/{idDocument}/{idLU}', [
	'uses' => 'App\Http\Controllers\LU\ReportController@reportStaticObject',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/{idLU}/dynamic', [
	'uses' => 'App\Http\Controllers\LU\ReportController@reportDynamic',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/{idLU}/dynamic/objects/{idDocument}', [
	'uses' => 'App\Http\Controllers\LU\ReportController@reportDynamicObjects',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/dynamic/object/{idDynamicObject}', [
	'uses' => 'App\Http\Controllers\LU\ReportController@reportDynamicObject',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/lu/{idLU?}', [
	'uses' => 'App\Http\Controllers\LU\ReportController@report',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('report/lu/sentences', [
	'uses' => 'App\Http\Controllers\LU\ReportController@sentences',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/constraints', [
	'uses' => 'App\Http\Controllers\LU\ConstraintController@constraints',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/constraints/formNew/{fragment?}', [
	'uses' => 'App\Http\Controllers\LU\ConstraintController@constraintsFormNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/constraints/grid', [
	'uses' => 'App\Http\Controllers\LU\ConstraintController@constraintsGrid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/list/forEvent', [
	'uses' => 'App\Http\Controllers\LU\BrowseController@listForEvent',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/list/forSelect', [
	'uses' => 'App\Http\Controllers\LU\BrowseController@listForSelect',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lu', [
	'uses' => 'App\Http\Controllers\LU\ResourceController@newLU',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/edit', [
	'uses' => 'App\Http\Controllers\LU\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/object', [
	'uses' => 'App\Http\Controllers\LU\ResourceController@object',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lu/{id}', [
	'uses' => 'App\Http\Controllers\LU\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lu/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\LU\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('lu/{id}', [
	'uses' => 'App\Http\Controllers\LU\ResourceController@update',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('luCandidate', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@resource',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lucandidate/search', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@tree',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('luCandidate/new', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@new',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('luCandidate', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@newLU',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('luCandidate/{id}', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@edit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('luCandidate/{id}/asLOME', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@asLOME',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('luCandidate/fes/{idFrame}', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@feCombobox',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('luCandidate/{id}', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@delete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('luCandidate', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@update',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('luCandidate/createLU', [
	'uses' => 'App\Http\Controllers\LU\LUCandidateController@createLU',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/semanticTypes', [
	'uses' => 'App\Http\Controllers\CE\SemanticTypeController@semanticTypes',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/relations/{idEntityRelation}/cxn/{idCxnBase}', [
	'uses' => 'App\Http\Controllers\CE\RelationController@relations',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/relations/{idEntityRelation}/formNew', [
	'uses' => 'App\Http\Controllers\CE\RelationController@relationsCEFormNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/relations/{idEntityRelation}/grid', [
	'uses' => 'App\Http\Controllers\CE\RelationController@gridRelationsCE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/constraints', [
	'uses' => 'App\Http\Controllers\CE\ConstraintController@constraints',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/constraints/formNew', [
	'uses' => 'App\Http\Controllers\CE\ConstraintController@constraintsFormNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/constraints/grid', [
	'uses' => 'App\Http\Controllers\CE\ConstraintController@constraintsGrid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('ce', [
	'uses' => 'App\Http\Controllers\CE\ResourceController@newCE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/edit', [
	'uses' => 'App\Http\Controllers\CE\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/main', [
	'uses' => 'App\Http\Controllers\CE\ResourceController@main',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('ce/{id}', [
	'uses' => 'App\Http\Controllers\CE\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\CE\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('ce/{id}', [
	'uses' => 'App\Http\Controllers\CE\ResourceController@update',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('ce/{id}/entries', [
	'uses' => 'App\Http\Controllers\CE\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relations/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationgroup/new', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@formNewRelationGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationgroup/{idRelationGroup}/edit', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@relationgroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationgroup/{idRelationGroup}/formEdit', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@formEditRelationGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relations/relationgroup/new', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@newRelationGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('relations/relationgroup', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@updateRelationGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('relations/relationgroup/{idRelationGroup}', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@deleteRelationGroup',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationtype/new', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@formNewRelationType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationtype/{idRelationType}/edit', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@relationtype',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationtype/{idRelationType}/formEdit', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@formEditRelationType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relations/relationtype/new', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@newRelationType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('relations/relationtype', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@updateRelationType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('relations/relationtype/{idRelationType}', [
	'uses' => 'App\Http\Controllers\Relations\ResourceController@deleteRelationType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationgroup/{id}/entries', [
	'uses' => 'App\Http\Controllers\Relations\EntryController@entriesRGP',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('relations/relationtype/{id}/entries', [
	'uses' => 'App\Http\Controllers\Relations\EntryController@entriesRTY',
	'as' => NULL,
	'middleware' => ['master'],
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

$router->post('report/multimodal/grid', [
	'uses' => 'App\Http\Controllers\Multimodal\ReportController@grid',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/multimodal/data', [
	'uses' => 'App\Http\Controllers\Multimodal\ReportController@data',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/multimodal/{idDocument?}/{view?}', [
	'uses' => 'App\Http\Controllers\Multimodal\ReportController@report',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('utils/importFullText', [
	'uses' => 'App\Http\Controllers\Utils\BrowseController@browse',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('utils/importFullText/search', [
	'uses' => 'App\Http\Controllers\Utils\BrowseController@search',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('utils/importFullText/{id}', [
	'uses' => 'App\Http\Controllers\Utils\ImportFullTextController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('utils/importFullText', [
	'uses' => 'App\Http\Controllers\Utils\ImportFullTextController@update',
	'as' => NULL,
	'middleware' => ['master'],
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

$router->get('group/listForSelect', [
	'uses' => 'App\Http\Controllers\Group\ResourceController@listForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('group/new', [
	'uses' => 'App\Http\Controllers\Group\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('group/{id}/edit', [
	'uses' => 'App\Http\Controllers\Group\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('group/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\Group\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('group', [
	'uses' => 'App\Http\Controllers\Group\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('group/new', [
	'uses' => 'App\Http\Controllers\Group\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('group/{id}', [
	'uses' => 'App\Http\Controllers\Group\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user', [
	'uses' => 'App\Http\Controllers\User\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/new', [
	'uses' => 'App\Http\Controllers\User\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\User\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\User\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/{id}/edit', [
	'uses' => 'App\Http\Controllers\User\ResourceController@edit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\User\ResourceController@formEdit',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('user/{id}/authorize', [
	'uses' => 'App\Http\Controllers\User\ResourceController@authorizeUser',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('user/{id}/deauthorize', [
	'uses' => 'App\Http\Controllers\User\ResourceController@deauthorizeUser',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user', [
	'uses' => 'App\Http\Controllers\User\ResourceController@update',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user/new', [
	'uses' => 'App\Http\Controllers\User\ResourceController@create',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('user/{id}', [
	'uses' => 'App\Http\Controllers\User\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/listForSelect', [
	'uses' => 'App\Http\Controllers\User\ResourceController@listForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@main',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/frame2', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@frame2',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/frame2PPM', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@frame2PPM',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/frame2NLG', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@frame2NLG',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/frame2Gesture', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@frame2Gesture',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/audition', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@audition',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/multi30k', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@multi30k',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/mcgovern', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@mcgovern',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('dashboard/gt', [
	'uses' => 'App\Http\Controllers\Dashboard\DashboardController@gt',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/qualia', [
	'uses' => 'App\Http\Controllers\Qualia\ReportController@main',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/qualia/data', [
	'uses' => 'App\Http\Controllers\Qualia\ReportController@data',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/qualia/{idQualia?}/{lang?}', [
	'uses' => 'App\Http\Controllers\Qualia\ReportController@report',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/grid', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{id}/subTypes', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@semanticTypes',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{id}/edit', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@get',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('semanticType/{idSemanticType}', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@masterDelete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/new', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@formNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('semanticType/new', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@new',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childAdd/{root}', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@childFormAdd',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childGrid', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@childGrid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('semanticType/{idEntity}/add', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@childAdd',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('semanticType/relation/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@childDelete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childSubTypeAdd/{root}', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@childFormAddSubType',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('semanticType/{idEntity}/addSubType', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@childAddSubType',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childSubTypeGrid', [
	'uses' => 'App\Http\Controllers\Qualia\ResourceController@childGridSubType',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('report/c5/grid', [
	'uses' => 'App\Http\Controllers\C5\ReportController@grid',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/c5/data', [
	'uses' => 'App\Http\Controllers\C5\ReportController@data',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/c5/content/{idConcept}/{lang?}', [
	'uses' => 'App\Http\Controllers\C5\ReportController@reportContent',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/c5/{idConcept?}/{lang?}', [
	'uses' => 'App\Http\Controllers\C5\ReportController@main',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('concept/list/forSelect', [
	'uses' => 'App\Http\Controllers\C5\ReportController@listForSelect',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/semanticType', [
	'uses' => 'App\Http\Controllers\SemanticType\ReportController@main',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/semanticType/data', [
	'uses' => 'App\Http\Controllers\SemanticType\ReportController@data',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/semanticType/{idSemanticType?}/{lang?}', [
	'uses' => 'App\Http\Controllers\SemanticType\ReportController@report',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@resource',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/grid', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{id}/subTypes', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@semanticTypes',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{id}/edit', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@get',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('semanticType/{idSemanticType}', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@masterDelete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/new', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@formNew',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('semanticType/new', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@new',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childAdd/{root}', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@childFormAdd',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childGrid', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@childGrid',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('semanticType/{idEntity}/add', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@childAdd',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('semanticType/relation/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@childDelete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childSubTypeAdd/{root}', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@childFormAddSubType',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('semanticType/{idEntity}/addSubType', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@childAddSubType',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{idEntity}/childSubTypeGrid', [
	'uses' => 'App\Http\Controllers\SemanticType\ResourceController@childGridSubType',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('semanticType/{id}/entries', [
	'uses' => 'App\Http\Controllers\SemanticType\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
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

$router->get('empty', [
	'uses' => 'App\Http\Controllers\Controller@empty',
	'as' => NULL,
	'middleware' => [],
	'where' => [],
	'domain' => NULL,
]);

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

$router->delete('message/{id}', [
	'uses' => 'App\Http\Controllers\Message\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('constraint/fe/{id}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@constraintFE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('constraint/fe/{idConstraintInstance}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@deleteConstraintFE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('constraint/lu/{id}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@constraintLU',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('constraint/lu/{idConstraintInstance}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@deleteConstraintLU',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('constraint/ce/{id}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@constraintCE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('constraint/ce/{idConstraintInstance}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@deleteConstraintCE',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('constraint/cxn/{id}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@constraintCxn',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('constraint/cxn/{idConstraintInstance}', [
	'uses' => 'App\Http\Controllers\Constraint\ConstraintController@deleteConstraintCxn',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('grapher/domain', [
	'uses' => 'App\Http\Controllers\Grapher\DomainController@domain',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('grapher/domain/graph/{idEntity?}', [
	'uses' => 'App\Http\Controllers\Grapher\DomainController@domainGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('grapher/framefe/graph/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Grapher\DomainController@frameFeGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('grapher/scenario', [
	'uses' => 'App\Http\Controllers\Grapher\ScenarioController@scenario',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('grapher/scenario/graph/{idEntity?}', [
	'uses' => 'App\Http\Controllers\Grapher\ScenarioController@scenarioGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('grapher/frame', [
	'uses' => 'App\Http\Controllers\Grapher\FrameController@frame',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('grapher/frame/graph/{idEntity?}', [
	'uses' => 'App\Http\Controllers\Grapher\FrameController@frameGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('grapher/framefe/graph/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Grapher\FrameController@frameFeGraph',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('grapher/frame/report/{idEntityFrame}', [
	'uses' => 'App\Http\Controllers\Grapher\FrameController@frameReport',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence/{id}/document', [
	'uses' => 'App\Http\Controllers\Sentence\DocumentController@document',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence/{id}/document/formNew', [
	'uses' => 'App\Http\Controllers\Sentence\DocumentController@documentFormNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence/{id}/document/grid', [
	'uses' => 'App\Http\Controllers\Sentence\DocumentController@documentGrid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('sentence/{id}/document/new', [
	'uses' => 'App\Http\Controllers\Sentence\DocumentController@documentNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('sentence/{id}/document/{idDocument}', [
	'uses' => 'App\Http\Controllers\Sentence\DocumentController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('sentence/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence/new', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@formSentenceNew',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence/{idSentence}', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@sentence',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sentence/{id}/editForm', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@editForm',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('sentence/new', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@newSentence',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('sentence', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@updateSentence',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('sentence/{idSentence}', [
	'uses' => 'App\Http\Controllers\Sentence\ResourceController@deleteSentence',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@grid',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/lemma/listForSelect', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@listForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/lemma/new', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@formNewLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/lemma/{idLemma}/lexemeentries', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@lexemeentries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/lemma/{idLemma}/{fragment?}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@lemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon/lemma/new', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@newLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('lexicon/lemma/{idLemma}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@updateLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon/lemma/{idLemma}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@deleteLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/lexeme/new', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@formNewLexeme',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/lexeme/{idLexeme}/wordforms', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@wordforms',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon/lexeme/{idLexeme}/{fragment?}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@lexeme',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon/lexeme/new', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@newLexeme',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('lexicon/lexeme/{idLexeme}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@updateLexeme',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon/lexeme/{idLexeme}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@deleteLexeme',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon/lexemeentry/new', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@newLexemeEntry',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon/lexemeentries/{idLexemeEntry}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@deleteLexemeEntry',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon/wordform/new', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@newWordform',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon/wordform/{idWordForm}', [
	'uses' => 'App\Http\Controllers\Lexicon\ResourceController@deleteWordform',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon3/search', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@search',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/lemma/listForSearch', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@listForSearch',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/lemma/new', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@formNewLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon3/lemma/new', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@newLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/lemma/{idLemma}/expressions', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@expressions',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/lemma/{idLemma}', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@lemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('lexicon3/lemma', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@updateLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon3/lemma/{idLexicon}', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@deleteLemma',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/expression/listForSelect', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@listExpressionForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon3/expression/new', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@newExpression',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon3/expression/{idLexiconExpression}', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@deleteLexiconExpression',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/morpheme/listForSelect', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@listMorphemeForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/form/new', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@formNewForm',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon3/form/new', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@newForm',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon3/wordform/{idWordForm}', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@deleteWordform',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/form/{idLexicon}', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@lexicon',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('lexicon3/lexicon', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@updateLexicon',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/feature/listForSelect', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@listFeatureForSelect',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('lexicon3/feature/new', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@newFeature',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('lexicon3/feature/{idLexicon}', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@features',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('lexicon3/feature/{idLexicon}/{idUDFeature}', [
	'uses' => 'App\Http\Controllers\Lexicon\Resource3Controller@deleteFeature',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/semanticTypes', [
	'uses' => 'App\Http\Controllers\Frame\SemanticTypeController@semanticTypes',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/fes', [
	'uses' => 'App\Http\Controllers\Frame\FEController@fes',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/fes/formNew', [
	'uses' => 'App\Http\Controllers\Frame\FEController@formNewFE',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/fes/grid', [
	'uses' => 'App\Http\Controllers\Frame\FEController@gridFE',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/lus', [
	'uses' => 'App\Http\Controllers\Frame\LUController@lus',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/lus/formNew', [
	'uses' => 'App\Http\Controllers\Frame\LUController@formNewLU',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/lus/grid', [
	'uses' => 'App\Http\Controllers\Frame\LUController@gridLU',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/relations', [
	'uses' => 'App\Http\Controllers\Frame\RelationController@relations',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/relations/formNew', [
	'uses' => 'App\Http\Controllers\Frame\RelationController@formNewRelation',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/relations/grid', [
	'uses' => 'App\Http\Controllers\Frame\RelationController@gridRelation',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/classification', [
	'uses' => 'App\Http\Controllers\Frame\ClassificationController@classification',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/classification/formFramalType', [
	'uses' => 'App\Http\Controllers\Frame\ClassificationController@formFramalType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/classification/formFramalDomain', [
	'uses' => 'App\Http\Controllers\Frame\ClassificationController@formFramalDomain',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('frame/classification/domain', [
	'uses' => 'App\Http\Controllers\Frame\ClassificationController@framalDomain',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('frame/classification/type', [
	'uses' => 'App\Http\Controllers\Frame\ClassificationController@framalType',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/frame/script/{file}', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@scripts',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/frame', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@browse',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('report/frame/search', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@tree',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/frame_lu/search', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@browseFrameLU',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('report/frame_lu/search', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@postFrameLU',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/frame/{idFrame}/{lang?}', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@report',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/list/forSelect', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@listForSelect',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/listScenario/forSelect', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@listScenarioForSelect',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('report/frame/static/object/{idDocument}/{idImage}/{idFrame}', [
	'uses' => 'App\Http\Controllers\Frame\ReportController@getStaticObject',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame', [
	'uses' => 'App\Http\Controllers\Frame\BrowseController@browse',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('frame/search', [
	'uses' => 'App\Http\Controllers\Frame\BrowseController@tree',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/feRelations', [
	'uses' => 'App\Http\Controllers\Frame\FEInternalRelationController@feRelations',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/feRelations/formNew/{error?}', [
	'uses' => 'App\Http\Controllers\Frame\FEInternalRelationController@formNewFERelations',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/feRelations/grid', [
	'uses' => 'App\Http\Controllers\Frame\FEInternalRelationController@gridFERelations',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/new', [
	'uses' => 'App\Http\Controllers\Frame\ResourceController@new',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('frame', [
	'uses' => 'App\Http\Controllers\Frame\ResourceController@store',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('frame/{idFrame}', [
	'uses' => 'App\Http\Controllers\Frame\ResourceController@delete',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}', [
	'uses' => 'App\Http\Controllers\Frame\ResourceController@get',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('frame/{id}/entries', [
	'uses' => 'App\Http\Controllers\Frame\EntryController@entries',
	'as' => NULL,
	'middleware' => ['master'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('relation/feinternal/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Relation\FEInternalController@deleteFERelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relation/feinternal', [
	'uses' => 'App\Http\Controllers\Relation\FEInternalController@newFERelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('relation/cxn/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Relation\CxnController@deleteFrameRelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relation/cxn', [
	'uses' => 'App\Http\Controllers\Relation\CxnController@newFrameRelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('relation/fe/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Relation\FEController@deleteFERelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relation/fe', [
	'uses' => 'App\Http\Controllers\Relation\FEController@newFERelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('relation/ce/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Relation\CEController@deleteFERelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relation/ce', [
	'uses' => 'App\Http\Controllers\Relation\CEController@newCERelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('relation/frame/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\Relation\FrameController@deleteFrameRelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('relation/frame', [
	'uses' => 'App\Http\Controllers\Relation\FrameController@newFrameRelation',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);
