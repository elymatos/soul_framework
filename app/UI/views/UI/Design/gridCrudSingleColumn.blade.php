<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoTools']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="crud-grid single-column">
                        <div class="bg-gray-100">
                            Single column
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
