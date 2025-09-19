<?php

namespace App\Http\Controllers\Lexicon;

use App\Data\ComboBox\QData;
use App\Data\Lexicon\CreateLemmaData;
use App\Data\Lexicon\CreateLexemeData;
use App\Data\Lexicon\CreateLexemeEntryData;
use App\Data\Lexicon\CreateWordformData;
use App\Data\Lexicon\SearchData;
use App\Data\Lexicon\UpdateLayerGroupData;
use App\Data\Lexicon\UpdateLexemeData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use App\Repositories\Lemma;
use App\Repositories\Lexeme;
use App\Services\AppService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;

#[Middleware('master')]
class ResourceController extends Controller
{
    #[Get(path: '/lexicon')]
    public function browse()
    {
        $search = session('searchLexicon') ?? SearchData::from();

        return view('Lexicon.resource', [
            'search' => $search,
        ]);
    }

    #[Get(path: '/lexicon/grid/{fragment?}')]
    #[Post(path: '/lexicon/grid/{fragment?}')]
    public function grid(SearchData $search, ?string $fragment = null)
    {
        $view = view('Lexicon.grid', [
            'search' => $search,
            'sentences' => [],
        ]);

        return is_null($fragment) ? $view : $view->fragment('search');
    }

    /*------
      Lemma
      ------ */

    #[Get(path: '/lexicon/lemma/listForSelect')]
    public function listForSelect(QData $data)
    {
        $name = (strlen($data->q) > 2) ? $data->q : 'none';

        return ['results' => Criteria::byFilterLanguage('view_lemma', ['name', 'startswith', trim($name)])
            ->select('idLemma', 'fullName as name')
            ->orderby('name')->all()];
    }

    #[Get(path: '/lexicon/lemma/new')]
    public function formNewLemma()
    {
        return view('Lexicon.formNewLemma');
    }

    #[Get(path: '/lexicon/lemma/{idLemma}/lexemeentries')]
    public function lexemeentries(int $idLemma)
    {
        $lemma = Lemma::byId($idLemma);
        $lexemeentries = Criteria::table('lexemeentry as le')
            ->join('lexeme', 'le.idLexeme', '=', 'lexeme.idLexeme')
            ->join('pos', 'lexeme.idPOS', '=', 'pos.idPOS')
            ->where('le.idLemma', $idLemma)
            ->select('le.*', 'lexeme.name as lexeme', 'pos.pos')
            ->orderBy('le.lexemeorder')
            ->all();

        return view('Lexicon.lexemeentries', [
            'lemma' => $lemma,
            'lexemeentries' => $lexemeentries,
        ]);
    }

    #[Get(path: '/lexicon/lemma/{idLemma}/{fragment?}')]
    public function lemma(int $idLemma, ?string $fragment = null)
    {
        $lemma = Lemma::byId($idLemma);
        $lexemeentries = Criteria::table('lexemeentry as le')
            ->join('lexeme', 'le.idLexeme', '=', 'lexeme.idLexeme')
            ->join('pos', 'lexeme.idPOS', '=', 'pos.idPOS')
            ->where('le.idLemma', $idLemma)
            ->select('le.*', 'lexeme.name as lexeme', 'pos.pos')
            ->orderBy('le.lexemeorder')
            ->all();
        $view = view('Lexicon.lemma', [
            'lemma' => $lemma,
            'lexemeentries' => $lexemeentries,
        ]);

        return is_null($fragment) ? $view : $view->fragment($fragment);
    }

    #[Post(path: '/lexicon/lemma/new')]
    public function newLemma(CreateLemmaData $data)
    {
        try {
            $exists = Criteria::table('lemma')
                ->whereRaw("name = '{$data->name}' collate 'utf8mb4_bin'")
                ->where('idPOS', $data->idPOS)
                ->where('idLanguage', $data->idLanguage)
                ->first();
            if (! is_null($exists)) {
                throw new \Exception('Lemma already exists.');
            }
            $newLemma = json_encode([
                'name' => $data->name,
                'idPOS' => $data->idPOS,
                'idLanguage' => $data->idLanguage,
            ]);
            $idLemma = Criteria::function('lemma_create(?)', [$newLemma]);
            $lemma = Lemma::byId($idLemma);
            $lexemeentries = Criteria::table('lexemeentry as le')
                ->join('lexeme', 'le.idLexeme', '=', 'lexeme.idLexeme')
                ->where('le.idLemma', $idLemma)
                ->select('le.*', 'lexeme.name as lexeme')
                ->orderBy('le.lexemeorder')
                ->all();
            $view = view('Lexicon.lemma', [
                'lemma' => $lemma,
                'lexemeentries' => $lexemeentries,
            ]);

            return $view->fragment('content');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Put(path: '/lexicon/lemma/{idLemma}')]
    public function updateLemma(UpdateLayerGroupData $data)
    {
        try {
            Criteria::table('lemma')
                ->where('idLemma', $data->idLemma)
                ->update([
                    'name' => $data->name,
                    'idPOS' => $data->idPOS,
                ]);

            return $this->renderNotify('success', 'Lemma updated.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/lexicon/lemma/{idLemma}')]
    public function deleteLemma(string $idLemma)
    {
        try {
            Criteria::function('lemma_delete(?,?)', [$idLemma, AppService::getCurrentIdUser()]);
            $this->trigger('reload-gridLexicon');

            return $this->renderNotify('success', 'Lemma removed.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    /*------
      Lexeme
      ------ */

    #[Get(path: '/lexicon/lexeme/new')]
    public function formNewLexeme()
    {
        return view('Lexicon.formNewLexeme');
    }

    #[Get(path: '/lexicon/lexeme/{idLexeme}/wordforms')]
    public function wordforms(int $idLexeme)
    {
        $lexeme = Lexeme::byId($idLexeme);
        $wordforms = Criteria::table('wordform')
            ->where('idLexeme', $idLexeme)
            ->orderBy('form')
            ->all();

        return view('Lexicon.wordforms', [
            'lexeme' => $lexeme,
            'wordforms' => $wordforms,
        ]);
    }

    #[Get(path: '/lexicon/lexeme/{idLexeme}/{fragment?}')]
    public function lexeme(int $idLexeme, ?string $fragment = null)
    {
        $lexeme = Lexeme::byId($idLexeme);
        $wordforms = Criteria::table('wordform')
            ->where('idLexeme', $idLexeme)
            ->orderBy('form')
            ->all();
        $view = view('Lexicon.lexeme', [
            'lexeme' => $lexeme,
            'wordforms' => $wordforms,
        ]);

        return is_null($fragment) ? $view : $view->fragment('content');
    }

    #[Post(path: '/lexicon/lexeme/new')]
    public function newLexeme(CreateLexemeData $data)
    {
        try {
            $exists = Criteria::table('lexeme')
                ->whereRaw("name = '{$data->name}' collate 'utf8mb4_bin'")
                ->where('idPOS', $data->idPOS)
                ->where('idLanguage', $data->idLanguage)
                ->first();
            if (! is_null($exists)) {
                throw new \Exception('Lexeme already exists.');
            }
            $newLexeme = json_encode([
                'name' => $data->name,
                'idPOS' => $data->idPOS,
                'idLanguage' => $data->idLanguage,
            ]);
            $idLexeme = Criteria::function('lexeme_create(?)', [$newLexeme]);
            $lexeme = Lexeme::byId($idLexeme);
            $wordforms = Criteria::table('wordform')
                ->where('idLexeme', $idLexeme)
                ->orderBy('form')
                ->all();
            $view = view('Lexicon.lexeme', [
                'lexeme' => $lexeme,
                'wordforms' => $wordforms,
            ]);

            return $view->fragment('content');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Put(path: '/lexicon/lexeme/{idLexeme}')]
    public function updateLexeme(UpdateLexemeData $data)
    {
        try {
            Criteria::table('lexeme')
                ->where('idLexeme', $data->idLexeme)
                ->update([
                    'name' => $data->name,
                    'idPOS' => $data->idPOS,
                ]);

            return $this->renderNotify('success', 'Lexeme updated.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/lexicon/lexeme/{idLexeme}')]
    public function deleteLexeme(string $idLexeme)
    {
        try {
            Criteria::function('lexeme_delete(?,?)', [$idLexeme, AppService::getCurrentIdUser()]);
            $this->trigger('reload-gridLexicon');

            return $this->renderNotify('success', 'Lexeme removed.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    /*-----------
      LexemeEntry
      ----------- */

    #[Post(path: '/lexicon/lexemeentry/new')]
    public function newLexemeEntry(CreateLexemeEntryData $data)
    {
        try {
            if ($data->idLexeme) {
                Criteria::table('lexemeentry')->insert([
                    'idLemma' => $data->idLemma,
                    'idLexeme' => $data->idLexeme,
                    'lexemeOrder' => $data->lexemeOrder,
                    'headWord' => $data->headWord,
                    'breakBefore' => $data->breakBefore,
                ]);
                $this->trigger('reload-gridLexemeEntry');

                return $this->renderNotify('success', 'Lexeme added.');
            } else {
                throw new \Exception('Lexeme not found.');
            }
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/lexicon/lexemeentries/{idLexemeEntry}')]
    public function deleteLexemeEntry(string $idLexemeEntry)
    {
        try {
            Criteria::deleteById('lexemeentry', 'idLexemeEntry', $idLexemeEntry);
            $this->trigger('reload-gridLexemeEntry');

            return $this->renderNotify('success', 'Lexeme removed.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    /*--------
      Wordform
      -------- */
    #[Post(path: '/lexicon/wordform/new')]
    public function newWordform(CreateWordformData $data)
    {
        try {
            Criteria::table('wordform')->insert([
                'idLexeme' => $data->idLexemeWordform,
                'form' => $data->form,
                'md5' => md5($data->form),
            ]);
            $this->trigger('reload-gridWordforms');

            return $this->renderNotify('success', 'Wordform created.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/lexicon/wordform/{idWordForm}')]
    public function deleteWordform(string $idWordForm)
    {
        try {
            Criteria::deleteById('wordform', 'idWordForm', $idWordForm);
            $this->trigger('reload-gridWordforms');

            return $this->renderNotify('success', 'Wordform removed.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }
}
