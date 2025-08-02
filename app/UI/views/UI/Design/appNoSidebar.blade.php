<x-layout::index>
    <div class="app-layout no-sidebar">
        @include('layouts.header')
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoSidebar']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    app-layout > no-sidebar | app-main | page-content > content-container
                </div>
            </div>
        </main>
        <aside class="app-tools">
            app-layout > no-sidebar | aside app-tools
        </aside>
    </div>
</x-layout::index>
