<?php

namespace App\Http\Controllers\Annotation;


use App\Data\Annotation\Session\SessionData;
use App\Http\Controllers\Controller;
use App\Services\Annotation\SessionService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware("auth")]
class SessionController extends Controller
{
    #[Get(path: '/annotation/session/script/{folder}')]
    public function jsObjects(string $folder)
    {
        return response()
            ->view("Annotation.Session.Scripts.{$folder}")
            ->header('Content-type', 'text/javascript');
    }
    #[Post(path: '/annotation/session/start')]
    public function sessionStart(SessionData $data) {
        debug("start",$data);
        $session = SessionService::startSession($data);
//        return $this->renderNotify("success", "Session started.");
        return response()->json([
            'success' => true,
            'session_token' => '',
            'startedAt' => $data->timestamp->toJSON()
        ]);
    }

    #[Post(path: '/annotation/session/end')]
    public function sessionEnd(SessionData $data) {
        debug("end",$data);
        $session = SessionService::endSession($data);
//        return $this->renderNotify("success", "Session ended.");
        return response()->json([
            'success' => true,
            'session_token' => '',
            'endedAt' => $data->timestamp->toJSON()
        ]);
    }

}

