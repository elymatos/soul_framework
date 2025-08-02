<x-layout::index>
    <div class="app-layout minimal">
        @include('layouts.header')
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appMinimal']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    app-layout > minimal | app-main | page-content > content-container
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
