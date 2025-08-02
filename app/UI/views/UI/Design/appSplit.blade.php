<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoTools']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <h3>Horizontal</h3>
                    <div class="split-layout horizontal bg-gray-200">
                        <div class="split-panel">
                            Left content
                        </div>
                        <div class="split-panel">
                            Right content
                        </div>
                    </div>
                    <h3>Vertical</h3>
                    <div class="split-layout vertical bg-gray-200">
                        <div class="split-panel">
                            Top content
                        </div>
                        <div class="split-panel">
                            Bottom content
                        </div>
                    </div>
                    <h3>Thirds</h3>
                    <div class="split-layout thirds bg-gray-200">
                        <div class="split-panel">
                            Left content
                        </div>
                        <div class="split-panel">
                            Right content
                        </div>
                    </div>
                    <h3>Golden</h3>
                    <div class="split-layout golden bg-gray-200">
                        <div class="split-panel">
                            Left content
                        </div>
                        <div class="split-panel">
                            Right content
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
