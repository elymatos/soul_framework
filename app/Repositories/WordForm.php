<?php

namespace App\Repositories;

use App\Database\Criteria;
use App\Services\AppService;
use Illuminate\Support\Facades\DB;

class WordForm
{
    public static function wordHasLU(string $wordform): bool
    {
        if (trim($wordform) == '') {
            return false;
        }
        $idLanguage = AppService::getCurrentIdLanguage();
        $wf1 = mb_strtolower($wordform);
        if ($wf1 == "'") {
            $wf1 = "\'";
        }
        $r = DB::select("
                    select l.form,count(l.idLU) as n
                    from view_lexicon l
                    where (l.form = '{$wf1}' collate 'utf8mb4_bin' )
                    and (idLanguageLM = {$idLanguage})
                    group by l.form
                    having count(l.idLU) > 0
                ");
        return !empty($r);
    }

    public static function hasLU(array $wordformList): bool
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        $list = [];
        foreach ($wordformList as $wf) {
            if ($wf != '') {
                $wf1 = str_replace("'", "\'", $wf);
                $r = DB::select("
                    select l.form,count(l.idLU) as n
                    from view_lexicon l
                    where (l.form = '{$wf1}')
                    and (idLanguageLM = {$idLanguage})
                    group by l.form
                    having count(l.idLU) > 0

                ");
                if (count($r) > 0) {
                    $list[$wf] = $r[0]['n'];
                }
            }
        }
        return !empty($list);
    }

    public static function getLUs(string $wordform, int $idLanguageBase = null)
    {
        if (trim($wordform) == '') {
            return [];
        }
        $idLanguage = AppService::getCurrentIdLanguage();
        $wf1 = mb_strtolower(str_replace("'", "\'", $wordform));
        $criteria = Criteria::table("view_lexicon as l")
            ->distinct()
            ->select("idLU", "lu", "senseDescription", "frame.name as frameName")
            ->join("view_frame as frame", "l.idFrame", "=", "frame.idFrame")
            ->whereRaw("l.form = '{$wf1}'  collate 'utf8mb4_bin'")
            ->where("l.idLanguageLM", "=", $idLanguageBase ?? $idLanguage)
            ->where("l.position", "=", 1)
            ->where("frame.idLanguage", "=", $idLanguage)
            ->orderBy("frame.name")
            ->orderBy("l.lu");
        return $criteria->all();
    }

    public static function listLU(array $wordformList)
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        $list = [];
        foreach ($wordformList as $i => $wf) {
            if ($wf != '') {
                $wf1 = str_replace("'", "\'", $wf);
                $criteria = self::getCriteria()
                    ->distinct();
                $criteria->select([
                    'lexeme.lexemeEntries.lemma.lus.idLU',
                    'lexeme.lexemeEntries.lemma.lus.name',
                    'lexeme.lexemeEntries.lemma.lus.frame.name as frameName'
                ]);
                $criteria->where("form", "=", $wf1);
                $criteria->where("lexeme.lexemeEntries.lemma.idLanguage", "=", $idLanguage);
                $criteria->where("lexeme.lexemeEntries.lemma.lus.frame.idLanguage", "=", $idLanguage);
                $criteria->where("lexeme.lexemeEntries.headWord", "=", 1);
                $criteria->orderBy("lexeme.lexemeEntries.lemma.lus.frame.name,lexeme.lexemeEntries.lemma.lus.name");
                $r = $criteria->all();
                if (count($r)) {
                    $list[$wf] = $r;
                }
            }
        }
        return $list;
    }

}

