<?php

namespace App\Http\Controllers\LU;

use App\Data\LU\ReframingData;
use App\Data\LUCandidate\CreateData;
use App\Data\LUCandidate\SearchData;
use App\Data\LUCandidate\UpdateData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use App\Repositories\Frame;
use App\Repositories\Lemma;
use App\Repositories\LU;
use App\Repositories\LUCandidate;
use App\Repositories\User;
use App\Services\AppService;
use App\Services\MessageService;
use Carbon\Carbon;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;

#[Middleware(name: 'auth')]
class ReframingController extends Controller
{

    #[Get(path: '/reframing')]
    public function reframing(int|string $idLU = '')
    {
        $search = session('searchLU') ?? SearchData::from();
        if (($idLU == 'list') || ($idLU == '')) {
            return view("LU.Reframing.main", [
                'search' => $search
            ]);
        } else {
            $lu = LU::byId($idLU);
            $search->lu = $lu->name;
            return view("LU.Reframing.main", [
                'search' => $search,
                'idLU' => $idLU
            ]);
        }
    }

    #[Post(path: '/reframing/grid')]
    public function grid(SearchData $search)
    {
        return view("LU.Reframing.grid", [
            'search' => $search,
        ]);
    }

    #[Get(path: '/reframing/data')]
    public function data(SearchData $search)
    {
        $lus = [];
        if ($search->lu != '') {
            $lus = Criteria::byFilterLanguage("view_lu",
                ['name', "startswith", $search->lu])
                ->select("idLU", "name", "frameName")
                ->orderBy('name')
                ->all();
        }
        return $lus;
    }

    #[Get(path: '/reframing/lu/{idLU}')]
    public function reframingLU(string $idLU)
    {
        $idUser = AppService::getCurrentIdUser();
        $user = User::byId($idUser);
        $isMaster = User::isManager($user) || User::isMemberOf($user, 'MASTER');
        $lu = LU::byId($idLU);
        $data = [
            'lu' => $lu,
            'language' => Criteria::byId("language", "idLanguage", $lu->idLanguage),
            'isMaster' => $isMaster,
        ];
        return view("LU.Reframing.reframing", $data);
    }

    #[Get(path: '/reframing/edit/{idLU}/{idNewFrame}')]
    public function reframingEdit(string $idLU, string $idNewFrame)
    {
        $lu = LU::byId($idLU);
        $alreadyExists = false;
        $exists = Criteria::table("lu")
            ->where("idLemma", $lu->idLemma)
            ->where("idFrame", $idNewFrame)
            ->first();
        if (!is_null($exists)) {
            $alreadyExists = true;
        }
        $data = [
            'lu' => $lu,
            'idNewFrame' => $idNewFrame,
            'alreadyExists' => $alreadyExists,
            'language' => Criteria::byId("language", "idLanguage", $lu->idLanguage),
        ];
        return view("LU.Reframing.edit", $data);
    }

    #[Get(path: '/reframing/fes/{idLU}/{idNewFrame}')]
    public function reframingFEs(string $idLU, string $idNewFrame)
    {
        $newFrame = Frame::byId($idNewFrame);
        $lu = LU::byId($idLU);
        $as = Criteria::table("view_annotationset as a")
            ->where("a.idLU", $idLU)
            ->select("a.idAnnotationSet")
            ->all();
        $idAS = collect($as)->pluck("idAnnotationSet")->toArray();
        $afe = Criteria::table("view_annotation_text_fe as afe")
            ->whereIN("afe.idAnnotationSet", $idAS)
            ->distinct()
            ->select("afe.idFrameElement")
            ->all();
        $idFE = collect($afe)->pluck("idFrameElement")->toArray();
        $fes = Criteria::table("view_frameelement as fe")
            ->where("fe.idLanguage", AppService::getCurrentIdLanguage())
            ->whereIN("fe.idFrameElement", $idFE)
            ->distinct()
            ->select("fe.idFrameElement", "fe.name", "fe.coreType", "fe.idColor", "fe.idEntity")
            ->orderBy("fe.name")
            ->all();
        $data = [
            'lu' => $lu,
            'idNewFrame' => $idNewFrame,
            'newFrame' => $newFrame,
            'fes' => $fes,
            'countAS' => count($as),
            'language' => Criteria::byId("language", "idLanguage", $lu->idLanguage),
        ];
//        debug($data);
        return view("LU.Reframing.fes", $data);
    }

    #[Put(path: '/reframing')]
    public function update(ReframingData $data)
    {
        try {
            LU::reframing($data);
            return $this->renderNotify("success", "Reframing done.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }

    }

    /*
    #[Get(path: '/reframing/grid/{fragment?}')]
    #[Post(path: '/reframing/grid/{fragment?}')]
    public function grid2(SearchData $search, ?string $fragment = null)
    {
        $view = view("LUCandidate.grid", [
            'search' => $search,
        ]);
        return (is_null($fragment) ? $view : $view->fragment('search'));
    }

    #[Get(path: '/reframing/new')]
    public function new()
    {
        return view("LUCandidate.formNew");
    }

    #[Post(path: '/reframing')]
    public function newLU(CreateData $data)
    {
        try {
            debug($data);
            if ((is_null($data->idLemma) || ($data->idLemma == 0))) {
                throw new \Exception("Lemma is required");
            } else {
                $lemma = Lemma::byId($data->idLemma);
                $data->name = $lemma->name;
                Criteria::table("lucandidate")
                    ->insert($data->toArray());
                $this->trigger('reload-gridLUCandidate');
                return $this->renderNotify("success", "LU Candidate created.");
            }
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Get(path: '/reframing/{id}/edit')]
    public function edit(string $id)
    {
        $idUser = AppService::getCurrentIdUser();
        $user = User::byId($idUser);
        $isManager = User::isManager($user);
        return view("LUCandidate.edit", [
            'luCandidate' => LUCandidate::byId($id),
            'isManager' => $isManager,
        ]);
    }

    #[Get(path: '/reframing/{id}/formEdit')]
    public function formEdit(string $id)
    {
        $idUser = AppService::getCurrentIdUser();
        $user = User::byId($idUser);
        $isManager = User::isManager($user);
        return view("LUCandidate.formEdit", [
            'luCandidate' => LUCandidate::byId($id),
            'isManager' => $isManager,
        ]);
    }

    #[Get(path: '/reframing/fes/{idFrame}')]
    public function feCombobox(int $idFrame)
    {
        return view("LUCandidate.fes", [
            'idFrame' => $idFrame
        ]);
    }

    #[Delete(path: '/reframing/{id}')]
    public function delete(string $id)
    {
        try {
            $luCandidate = LUCandidate::byId($id);
            MessageService::sendMessage((object)[
                'idUserFrom' => AppService::getCurrentIdUser(),
                'idUserTo' => $luCandidate->idUser,
                'class' => 'error',
                'text' => "LU candidate {$luCandidate->name} has been deleted.",
            ]);
            Criteria::deleteById("lucandidate", "idLUCandidate", $id);
//            $this->trigger('reload-gridLUCandidate');
//            return $this->renderNotify("success", "LU candidate deleted.");
            return $this->redirect("/reframing");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Put(path: '/reframing')]
    public function update(UpdateData $data)
    {
        debug($data);
        Criteria::table("lucandidate")
            ->where("idLUCandidate", $data->idLUCandidate)
            ->update($data->toArray());
        $luCandidate = LUCandidate::byId($data->idLUCandidate);
        MessageService::sendMessage((object)[
            'idUserFrom' => AppService::getCurrentIdUser(),
            'idUserTo' => $luCandidate->idUser,
            'class' => 'warning',
            'text' => "LU candidate {$luCandidate->name} has been updated.",
        ]);
        $this->trigger('reload-gridLUCandidate');
        return $this->renderNotify("success", "LU candidate updated.");
    }

    #[Post(path: '/reframing/createLU')]
    public function createLU(UpdateData $data)
    {
        try {
            $exists = Criteria::table("lu")
                ->where("idLemma",$data->idLemma)
                ->where("idFrame",$data->idFrame)
                ->first();
            if (!is_null($exists)) {
                throw new \Exception("LU already exists.");
            }
            Criteria::function('lu_create(?)', [$data->toJson()]);
            $luCandidate = LUCandidate::byId($data->idLUCandidate);
            $link = '';
            if ($luCandidate->idDocumentSentence) {
                $link = "/annotation/fullText/sentence/{$luCandidate->idDocumentSentence}";
            }
            if ($luCandidate->idDocument) {
                $link = "/annotation/staticBBox/{$luCandidate->idDocument}";
            }
            if ($link != '') {
                $link = "<a href=\"{$link}\">Link to annotation.</a>.";
            }
            MessageService::sendMessage((object)[
                'idUserFrom' => AppService::getCurrentIdUser(),
                'idUserTo' => $luCandidate->idUser,
                'class' => 'success',
                'text' => "LU candidate {$luCandidate->name} has been created as LU.  {$link} ",
            ]);
            Criteria::deleteById("lucandidate", "idLUCandidate", $data->idLUCandidate);
            return $this->renderNotify("success", "LU created.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }
    */

}
