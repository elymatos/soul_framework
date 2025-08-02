@php
    use Illuminate\Support\Facades\Auth;
    use App\Data\MenuData;
    use App\Services\AppService;

    $actions = config('webtool.actions');
    $isLogged = Auth::check();
    if ($isLogged) {
        $user = Auth::user();
        $userLevel = session('userLevel');
    }
    $currentLanguage = session('currentLanguage');
    $languages = config('webtool.user')[3]['language'][3];
    $profile = config('webtool.user')[3]['profile'][3];
    $hrefLogin = (env('AUTH0_CLIENT_ID') == 'auth0') ? '/auth0Login' : '/';

@endphp
<div class="app-sidebar">
    <div class="main">

        <div class="ui secondary vertical menu">
            @if($isLogged)
                <div
                    class="ui accordion"
                    x-init="$($el).accordion();"
                >
                    <div class="title d-flex flex-row user-menu item p-0">
                        <i class="dropdown icon m-0"></i>
                        <div class="user-avatar">{!! strtoupper($user->email[0]) !!}</div>
                        <div class="user-email">{{$user->email}}
                            <div class="user-level">{{$userLevel}} #{{$user->idUser}}</div>
                        </div>
                    </div>
                    <div class="content">
                        <a class="item" href="/profile">
                            Profile
                        </a>
                        <a class="item" href="/logout">
                            Logout
                        </a>
                    </div>
                </div>
            @endif
            @foreach($actions as $id => $action)
                @php
                    $menuData = MenuData::from([
                        'id' => $id . '_small',
                        'label' => $action[0],
                        'href' => $action[1],
                        'group' => $action[2],
                        'items' => $action[3]
                    ]);
                @endphp
                @if (AppService::checkAccess($menuData->group))
                    <a class="item" href="{{$menuData->href}}">
                        {!! $menuData->label !!}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
<div class="app-sidebar-footer">
    {!! config('webtool.footer') !!}
</div>
