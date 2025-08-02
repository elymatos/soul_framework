<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoTools']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container wide bg-gray-200">
                    app-layout > no-tools | app-main | page-content > content-container wide
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
