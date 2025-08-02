<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <div class="page-content">
                <div class=" center-container-with-sidebar content-container wide h-full">
                    <div>
                        <div class="pb-4">
                            <img src="/images/fnbr_logo_alpha.png" width="240" />
                        </div>
                        <div>
                            <a class="ui button login">Sign In</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <script>
            $(function() {
                $(".ui.button.login").click(function(e) {
                    e.preventDefault();
                    window.location = "/auth0Login";
                });
            });
        </script>
    </div>
</x-layout::index>



