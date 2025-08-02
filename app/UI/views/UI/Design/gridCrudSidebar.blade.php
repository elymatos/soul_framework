<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoTools']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="crud-grid with-sidebar">
                        <div class="sidebar-content bg-gray-100">
                            Filters, categories, quick actions
                        </div>
                        <div class="main-content bg-gray-200">
                            Data table or cards
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
