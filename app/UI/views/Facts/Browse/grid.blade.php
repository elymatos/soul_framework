@if(isset($has_filters) && $has_filters && isset($filter_summary))
<div class="ui info message">
    <div class="header">Active Filters</div>
    <ul class="list">
        @foreach($filter_summary as $key => $value)
        <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(isset($facts) && count($facts) > 0)
<!-- Results Summary -->
<div class="ui secondary segment">
    <div class="ui grid">
        <div class="ten wide column">
            <div class="ui small statistic">
                <div class="value">{{ count($facts) }}</div>
                <div class="label">Facts Found</div>
            </div>
            @if(isset($pagination))
            <div class="ui small statistic">
                <div class="value">{{ $pagination['total'] }}</div>
                <div class="label">Total</div>
            </div>
            @endif
        </div>
        <div class="six wide column">
            @if(isset($pagination) && $pagination['total_pages'] > 1)
            <div class="ui right floated pagination menu">
                @if($pagination['has_prev'])
                <a class="item" onclick="changePage({{ $pagination['current_page'] - 1 }})">
                    <i class="left chevron icon"></i>
                </a>
                @endif
                
                <div class="item">
                    Page {{ $pagination['current_page'] }} of {{ $pagination['total_pages'] }}
                </div>
                
                @if($pagination['has_next'])
                <a class="item" onclick="changePage({{ $pagination['current_page'] + 1 }})">
                    <i class="right chevron icon"></i>
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Facts Grid -->
<div class="ui stackable cards">
    @foreach($facts as $fact)
    <div class="card fact-card" data-fact-id="{{ $fact['id'] }}">
        <div class="content">
            <div class="header">
                <div class="ui grid">
                    <div class="twelve wide column">
                        {{ $fact['statement'] }}
                    </div>
                    <div class="four wide column">
                        <div class="ui right floated dropdown">
                            <i class="ellipsis vertical icon"></i>
                            <div class="menu">
                                <a class="item" onclick="showFactDetails('{{ $fact['id'] }}')">
                                    <i class="eye icon"></i>
                                    View Details
                                </a>
                                <a class="item" href="/facts/{{ $fact['id'] }}/edit">
                                    <i class="edit icon"></i>
                                    Edit
                                </a>
                                <a class="item" onclick="showFactNetwork('{{ $fact['id'] }}')">
                                    <i class="sitemap icon"></i>
                                    Show Network
                                </a>
                                <div class="divider"></div>
                                <a class="item" onclick="confirmDeleteFact('{{ $fact['id'] }}', '{{ addslashes($fact['statement']) }}')">
                                    <i class="trash icon"></i>
                                    Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="meta">
                <div class="ui labels">
                    @if($fact['verified'])
                    <div class="ui green label">
                        <i class="checkmark icon"></i>
                        Verified
                    </div>
                    @endif
                    
                    <div class="ui label">
                        <i class="tag icon"></i>
                        {{ ucfirst($fact['fact_type']) }}
                    </div>
                    
                    @if($fact['domain'])
                    <div class="ui label">
                        <i class="folder icon"></i>
                        {{ $fact['domain'] }}
                    </div>
                    @endif
                    
                    <div class="ui label">
                        <i class="star icon"></i>
                        {{ ucfirst($fact['priority']) }}
                    </div>
                </div>
            </div>

            <!-- Confidence Bar -->
            <div class="confidence-bar {{ $fact['confidence'] >= 0.8 ? 'confidence-high' : ($fact['confidence'] >= 0.5 ? 'confidence-medium' : 'confidence-low') }}" 
                 style="width: {{ $fact['confidence'] * 100 }}%" 
                 title="Confidence: {{ number_format($fact['confidence'], 2) }}">
            </div>

            <!-- Triplet Display -->
            @if(isset($fact['concepts']) && is_array($fact['concepts']))
            <div class="triplet-display">
                @php
                    $subject = collect($fact['concepts'])->firstWhere('role', 'subject');
                    $predicate = collect($fact['concepts'])->firstWhere('role', 'predicate');
                    $object = collect($fact['concepts'])->firstWhere('role', 'object');
                    $modifiers = collect($fact['concepts'])->where('role', 'modifier');
                @endphp
                
                <div class="triplet-core">
                    @if($subject)
                    <span class="triplet-role role-subject">SUBJ:</span>
                    <strong>{{ $subject['name'] }}</strong>
                    @endif
                    
                    @if($predicate)
                    <span class="triplet-role role-predicate">PRED:</span>
                    <strong>{{ $predicate['name'] }}</strong>
                    @endif
                    
                    @if($object)
                    <span class="triplet-role role-object">OBJ:</span>
                    <strong>{{ $object['name'] }}</strong>
                    @endif
                </div>
                
                @if($modifiers->count() > 0)
                <div class="triplet-modifiers" style="margin-top: 5px; font-size: 0.9em;">
                    @foreach($modifiers as $modifier)
                    <span class="triplet-role role-modifier">MOD:</span>
                    <span>{{ $modifier['name'] }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            @if($fact['description'])
            <div class="description">
                {{ $fact['description'] }}
            </div>
            @endif

            @if($fact['tags'] && count($fact['tags']) > 0)
            <div class="extra">
                <div class="ui tiny labels">
                    @foreach($fact['tags'] as $tag)
                    <div class="ui label">{{ $tag }}</div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="extra content">
            <div class="ui mini statistics">
                <div class="statistic">
                    <div class="value">{{ $fact['concept_count'] }}</div>
                    <div class="label">Concepts</div>
                </div>
                <div class="statistic">
                    <div class="value">{{ number_format($fact['confidence'], 2) }}</div>
                    <div class="label">Confidence</div>
                </div>
            </div>

            <div class="right floated">
                <div class="ui tiny labels">
                    @if($fact['has_modifiers'])
                    <div class="ui orange label" title="Has modifiers">
                        <i class="plus icon"></i>
                    </div>
                    @endif
                    
                    @if($fact['has_temporal'])
                    <div class="ui purple label" title="Has temporal context">
                        <i class="clock icon"></i>
                    </div>
                    @endif
                    
                    @if($fact['has_spatial'])
                    <div class="ui teal label" title="Has spatial context">
                        <i class="map marker icon"></i>
                    </div>
                    @endif
                    
                    @if($fact['has_causal'])
                    <div class="ui brown label" title="Has causal context">
                        <i class="linkify icon"></i>
                    </div>
                    @endif
                </div>
            </div>

            <div class="meta">
                <span class="date">
                    <i class="calendar icon"></i>
                    Created {{ \Carbon\Carbon::parse($fact['created_at'])->diffForHumans() }}
                </span>
                @if($fact['source'])
                <span class="source" style="margin-left: 10px;">
                    <i class="external link icon"></i>
                    {{ $fact['source'] }}
                </span>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
@if(isset($pagination) && $pagination['total_pages'] > 1)
<div class="ui center aligned basic segment">
    <div class="ui pagination menu">
        @if($pagination['has_prev'])
        <a class="item" onclick="changePage(1)">
            <i class="angle double left icon"></i>
        </a>
        <a class="item" onclick="changePage({{ $pagination['current_page'] - 1 }})">
            <i class="angle left icon"></i>
        </a>
        @endif

        @php
        $start = max(1, $pagination['current_page'] - 2);
        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
        @endphp

        @for($i = $start; $i <= $end; $i++)
        <a class="item {{ $i == $pagination['current_page'] ? 'active' : '' }}" onclick="changePage({{ $i }})">
            {{ $i }}
        </a>
        @endfor

        @if($pagination['has_next'])
        <a class="item" onclick="changePage({{ $pagination['current_page'] + 1 }})">
            <i class="angle right icon"></i>
        </a>
        <a class="item" onclick="changePage({{ $pagination['total_pages'] }})">
            <i class="angle double right icon"></i>
        </a>
        @endif
    </div>
</div>
@endif

@else
<!-- No Results -->
<div class="ui placeholder segment">
    <div class="ui icon header">
        <i class="search icon"></i>
        No facts found
    </div>
    <div class="inline">
        @if(isset($has_filters) && $has_filters)
        <div class="ui primary button" onclick="clearAllFilters()">
            Clear Filters
        </div>
        @else
        <div class="ui primary button" onclick="window.location.href='/facts/create'">
            Create First Fact
        </div>
        @endif
    </div>
</div>
@endif

<script>
    // Initialize dropdowns in the grid
    $('.ui.dropdown').dropdown();

    function changePage(page) {
        const form = document.querySelector('form[hx-post="/facts/browse"]');
        
        // Add or update offset parameter
        let offsetInput = form.querySelector('input[name="offset"]');
        if (!offsetInput) {
            offsetInput = document.createElement('input');
            offsetInput.type = 'hidden';
            offsetInput.name = 'offset';
            form.appendChild(offsetInput);
        }
        
        const limit = parseInt(form.querySelector('select[name="limit"]').value) || 20;
        offsetInput.value = (page - 1) * limit;
        
        form.dispatchEvent(new Event('submit'));
    }

    function showFactNetwork(factId) {
        // Switch to network tab and load the fact
        $('.menu .item[data-tab="network"]').click();
        
        setTimeout(() => {
            if (window.tripletNetwork) {
                window.tripletNetwork.loadFactNetwork(factId, 2);
            }
        }, 500);
    }

    // Fact card hover effects
    document.querySelectorAll('.fact-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
</script>