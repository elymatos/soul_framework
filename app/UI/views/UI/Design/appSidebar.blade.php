<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appSidebar']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <h3>Left</h3>
                    <div class="sidebar-layout left">
                        <div class="sidebar-content">
                            Navigation, filters, metadata
                        </div>
                        <div class="main-content">
                            Primary content
                        </div>
                    </div>
                    <h3>Right</h3>
                    <div class="sidebar-layout right">
                        <div class="main-content">
                            Primary content
                        </div>
                        <div class="sidebar-content">
                            Navigation, filters, metadata
                        </div>
                    </div>
                    <h3>Left wide</h3>
                    <div class="sidebar-layout left wide-sidebar">
                        <div class="sidebar-content">
                            Navigation, filters, metadata
                        </div>
                        <div class="main-content">
                            Primary content
                        </div>
                    </div>
                    <h3>Left narrow</h3>
                    <div class="sidebar-layout left narrow-sidebar">
                        <div class="sidebar-content">
                            Navigation, filters, metadata
                        </div>
                        <div class="main-content">
                            Primary content
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</x-layout::index>
