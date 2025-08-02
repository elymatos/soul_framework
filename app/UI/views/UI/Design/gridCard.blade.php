<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','appNoTools']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <h3>Dense</h3>
                    <div class="card-grid dense">
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                    </div>
                    <h3>Wide</h3>
                    <div class="card-grid wide-cards">
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                    </div>
                    <h3>Small</h3>
                    <div class="card-grid small-cards">
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                    </div>
                    <h3>Single row</h3>
                    <div class="card-grid single-row">
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                        <div class="ui card">
                            <div class="content">
                                <div class="header">User Name</div>
                                <div class="description">User details...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
