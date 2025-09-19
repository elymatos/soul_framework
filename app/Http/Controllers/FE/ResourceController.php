<?php

namespace App\Http\Controllers\FE;

use App\Data\FE\CreateData;
use App\Data\FE\UpdateData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use App\Repositories\FrameElement;
use App\Services\AppService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;

#[Middleware(name: 'auth')]
class ResourceController extends Controller
{
    #[Post(path: '/fe')]
    public function newFE(CreateData $data)
    {
        debug($data);
        try {
            Criteria::function('fe_create(?, ?, ?, ?, ?)', [
                $data->idFrame,
                $data->nameEn,
                $data->coreType,
                $data->idColor,
                $data->idUser,
            ]);
            $this->trigger('reload-gridFE');

            return $this->renderNotify('success', 'FrameElement created.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Get(path: '/fe/{id}/edit')]
    public function edit(string $id)
    {
        return view('FE.edit', [
            'frameElement' => FrameElement::byId($id),
        ]);
    }

    #[Get(path: '/fe/{id}/main')]
    public function main(string $id)
    {
        $this->data->_layout = 'main';

        return $this->edit($id);
    }

    #[Delete(path: '/fe/{id}')]
    public function delete(string $id)
    {
        try {
            Criteria::function('fe_delete(?, ?)', [
                $id,
                AppService::getCurrentUser()->idUser,
            ]);
            $this->trigger('reload-gridFE');

            return $this->renderNotify('success', 'FrameElement deleted.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Get(path: '/fe/{id}/formEdit')]
    public function formEdit(string $id)
    {
        return view('FE.formEdit', [
            'frameElement' => FrameElement::byId($id),
        ]);
    }

    #[Put(path: '/fe/{id}')]
    public function update(string $id, UpdateData $data)
    {
        FrameElement::update($data);
        $this->trigger('reload-objectFE');

        return $this->renderNotify('success', 'FrameElement updated.');
    }
}
