<?php

namespace App\Http\Middleware;

use App\Services\AppService;
use App\Services\AuthUserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Data
{
    public function handle(Request $request, Closure $next): Response
    {
//        ddump('=================== in data middleware');
        $data = $request->all();
        $extraData = [];
        //debug($data);
//        foreach ($data as $id => $value) {
//            if (str_contains($id, '_') && ($id != '_token')) {
//                $var = explode('_', $id);
//                $attr = $var[1];
//                $extraData[$var[0]] ??= (object)[];
//                if (isset($var[2])) {
//                    $extra = $var[2];
//                    $extraData[$var[0]]->$attr ??= (object)[];
//                    $extraData[$var[0]]->$attr->$extra = $value ?? '';
//                } else {
//                    $extraData[$var[0]]->$attr = $value ?? '';
//                }
//            }
//        }
//        debug($extraData);
//        foreach($extraData as $var => $v) {
//            $request->request->add([$var => $v]);
//        }

            foreach ($data as $id => $value) {
            if (str_contains($id, '_') && ($id != '_token')) {
                $var = explode('_', $id);
                $attr = $var[1];
                $extraData[$var[0]] ??= [];
                if (isset($var[2])) {
                    $extra = $var[2];
                    $extraData[$var[0]][$attr] ??= [];
                    $extraData[$var[0]][$attr][$extra] = $value ?? '';
                } else {
                    $extraData[$var[0]][$attr] = $value ?? '';
                }
            }
        }
        $request->merge($extraData);

//        debug($data);
//        Manager::setData((object)$data);
//        debug($request->all());
        Manager::setData((object)$request->all());
        return $next($request);
    }
}
