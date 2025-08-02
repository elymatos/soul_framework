@php
    use Illuminate\Support\Facades\Auth;

    $actions = config('webtool.actions');
    $isLogged = Auth::check();
    if ($isLogged) {
        $user = Auth::user();
        $userLevel = session('userLevel');
    }
    $currentLanguage = session('currentLanguage');
    $languages = config('webtool.user')[3]['language'][3];
    $profile = config('webtool.user')[3]['profile'][3];
    $hrefLogin = (env('APP_AUTH') == 'auth0') ? '/auth0Login' : '/';

@endphp
<header class="app-header">
    <div class="header-left">
        <button class="mobile-menu-toggle">
            <i class="bars icon"></i>
        </button>
        <a href="/" class="logo">
            <div>
                <img src="/images/fnbr_logo_header_alpha.png" />
            </div>
            <span>{!! config('webtool.headerTitle') !!} <span class="header-version">{!! config('webtool.version') !!}</span></span>
        </a>
    </div>
    <div class="header-right">
        <div id="menuLanguage" class="ui dropdown pointing top left pr-3">
            {!! $currentLanguage->description !!}<i class="dropdown icon"></i>
            <div class="menu">
                @foreach($languages as $language)
                    <div class="item" hx-get="{{$language[1]}}" hx-trigger="click">{{$language[0]}}</div>
                @endforeach
            </div>
        </div>
    </div>
</header>
<script>
    $(function() {
        $("#menuLanguage").dropdown();
    });
</script>
