<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <div class="m-2">
                <div class="ui negative attached message">
                    <div class="header">
                        Error
                    </div>
                    <p>
                        {{$message}}
                    </p>
                </div>
                <div class="ui bottom attached negative message">
                    <a href="{{$goto}}">
                        <button
                            class="ui button negative"
                            type="button"
                        >{{$gotoLabel}}
                        </button>
                    </a>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
