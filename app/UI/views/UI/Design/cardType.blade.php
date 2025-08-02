<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','cardTypes']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <h3>Option Cards - Navigation and Selection</h3>
                    <div>
                        <p>Purpose: Primary navigation to different sections/features</p>
                        <p>When to use: Main landing pages, category selection, feature discovery</p>
                    </div>
                    <div class="ui card option-card" data-category="reports">
                        <div class="content">
                            <div class="option-card-icon reports">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="header">Reports & Analytics</div>
                            <div class="description">
                                Comprehensive analysis tools for FrameNet data including frames and lexical units.
                            </div>
                            <div class="option-card-meta">
                                <span class="option-card-count">1,230 reports</span>
                                <span class="option-card-updated">Updated 2h ago</span>
                            </div>
                        </div>
                    </div>

                    <h3>Data Cards - Entity Display</h3>
                    <div>Purpose: Display individual data entities (users, frames, constructions) When to use: Browse views, search results, entity listings</div>
                    <div class="ui card data-card" data-entity-id="123">
                        <div class="content">
                            <div class="data-card-header">
                                <div class="data-card-avatar">
                                    <i class="user icon"></i>
                                </div>
                                <div class="data-card-title">
                                    <div class="header">John Doe</div>
                                    <div class="meta">Administrator</div>
                                </div>
                                <div class="data-card-status">
                                    <div class="ui green label">Active</div>
                                </div>
                            </div>
                            <div class="description">
                                Research administrator specializing in semantic frame analysis and lexical unit categorization.
                            </div>
                            <div class="data-card-stats">
                                <div class="statistic">
                                    <div class="value">47</div>
                                    <div class="label">Annotations</div>
                                </div>
                                <div class="statistic">
                                    <div class="value">12</div>
                                    <div class="label">Reports</div>
                                </div>
                            </div>
                        </div>
                        <div class="extra content">
                            <div class="data-card-actions">
                                <button class="ui button mini">View</button>
                                <button class="ui button mini primary">Edit</button>
                            </div>
                        </div>
                    </div>

                    <h3>Summary Cards - Metrics and KPIs</h3>
                    <div>Purpose: Display key metrics, statistics, and performance indicators When to use: Dashboards, analytics pages, overview sections</div>
                    <div class="ui card summary-card primary">
                        <div class="content">
                            <div class="summary-card-header">
                                <div class="summary-card-icon">
                                    <i class="users icon"></i>
                                </div>
                                <div class="summary-card-trend positive">
                                    <i class="arrow up icon"></i>
                                    +12%
                                </div>
                            </div>
                            <div class="summary-card-value">
                                <div class="statistic">
                                    <div class="value">1,247</div>
                                    <div class="label">Total Users</div>
                                </div>
                            </div>
                            <div class="summary-card-description">
                                Active users in the last 30 days
                            </div>
                        </div>
                    </div>

                    <h3>Action Cards - Quick Actions</h3>
                    <div>Purpose: Provide quick access to common actions and tools When to use: Quick action panels, workflow shortcuts, tool palettes</div>

                    <div class="ui card action-card" data-action="export">
                        <div class="content">
                            <div class="action-card-icon">
                                <i class="download icon"></i>
                            </div>
                            <div class="header">Export Data</div>
                            <div class="description">
                                Download reports and data in various formats (CSV, JSON, XML)
                            </div>
                            <div class="action-card-shortcut">
                                <kbd>Ctrl</kbd> + <kbd>E</kbd>
                            </div>
                        </div>
                    </div>

                    <h3>Content Cards - Rich Content Display</h3>
                    <div>Purpose: Display rich content with images, videos, or complex layouts When to use: Media galleries, content libraries, article previews</div>


                    <div class="ui card content-card">
                        <div class="image">
                            <img src="frame-diagram.png" alt="Frame Diagram">
                            <div class="content-card-overlay">
                                <button class="ui button circular icon">
                                    <i class="play icon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="content">
                            <div class="header">Cooking Frame Analysis</div>
                            <div class="meta">
                                <span class="date">Updated 3 days ago</span>
                                <span class="category">Semantic Frames</span>
                            </div>
                            <div class="description">
                                Comprehensive analysis of the cooking semantic frame including core elements,
                                frame-to-frame relations, and lexical unit associations.
                            </div>
                        </div>
                        <div class="extra content">
                            <div class="content-card-tags">
                                <div class="ui label mini">cooking</div>
                                <div class="ui label mini">food preparation</div>
                                <div class="ui label mini">cuisine</div>
                            </div>
                        </div>
                    </div>

                    <h3>Form Cards - Input Containers</h3>
                    <div>Purpose: Group related form fields and inputs When to use: Multi-section forms, configuration panels, settings</div>

                    <div class="ui card form-card">
                        <div class="content">
                            <div class="header">
                                <i class="settings icon"></i>
                                Account Settings
                            </div>
                            <div class="description">
                                Configure your account preferences and security settings
                            </div>
                        </div>
                        <div class="content">
                            <div class="ui form">
                                <div class="field">
                                    <label>Display Name</label>
                                    <input type="text" value="John Doe">
                                </div>
                                <div class="field">
                                    <label>Email</label>
                                    <input type="email" value="john.doe@example.com">
                                </div>
                                <div class="field">
                                    <div class="ui checkbox">
                                        <input type="checkbox" id="notifications">
                                        <label for="notifications">Email notifications</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="extra content">
                            <div class="ui buttons">
                                <button class="ui button">Cancel</button>
                                <button class="ui button primary">Save Changes</button>
                            </div>
                        </div>
                    </div>

                    <h3>Status Cards - System Information</h3>
                    <div>Purpose: Display system status, alerts, and notifications When to use: System dashboards, monitoring panels, alert systems</div>
                    <div class="ui card status-card warning">
                        <div class="content">
                            <div class="status-card-header">
                                <div class="status-card-icon">
                                    <i class="exclamation triangle icon"></i>
                                </div>
                                <div class="status-card-level">Warning</div>
                                <div class="status-card-time">2 minutes ago</div>
                            </div>
                            <div class="header">High Memory Usage Detected</div>
                            <div class="description">
                                System memory usage has exceeded 85% threshold. Consider reviewing
                                active processes and optimizing memory allocation.
                            </div>
                            <div class="status-card-details">
                                <div class="detail-item">
                                    <strong>Current Usage:</strong> 87.3%
                                </div>
                                <div class="detail-item">
                                    <strong>Threshold:</strong> 85%
                                </div>
                            </div>
                        </div>
                        <div class="extra content">
                            <button class="ui button mini">View Details</button>
                            <button class="ui button mini primary">Optimize</button>
                        </div>
                    </div>


                </div>
            </div>
        </main>
    </div>
</x-layout::index>
