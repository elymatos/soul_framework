<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoTools']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="crud-grid three-panel">
                        <div class="bg-gray-100">
                            panel 1
                        </div>
                        <div class="bg-gray-200">
                            panel 2
                        </div>
                        <div class="bg-gray-100">
                            panel 3                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
