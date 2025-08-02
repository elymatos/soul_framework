<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoTools']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="crud-grid split-view">
                        <div class="bg-gray-100">
                            side 1
                        </div>
                        <div class="bg-gray-200">
                            side 2
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
