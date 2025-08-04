<div class="ui segment">
    <h3 class="ui header">
        <i class="sitemap icon"></i>
        Spreading Activation from "{{ $conceptName }}"
    </h3>
    
    @if(isset($error))
        <div class="ui negative message">
            <div class="header">Error</div>
            <p>{{ $error }}</p>
        </div>
    @endif
    
    @if(!empty($activationData['activatedConcepts']))
        <div class="ui info message">
            <div class="header">Activation Results</div>
            <p>Found {{ count($activationData['activatedConcepts']) }} concepts activated from "{{ $conceptName }}"</p>
        </div>
        
        <div class="ui relaxed divided list">
            @foreach($activationData['activatedConcepts'] as $activated)
                <div class="item">
                    <div class="content">
                        <a href="/soul/browse/{{ urlencode($activated['name']) }}" class="header">
                            {{ $activated['name'] }}
                        </a>
                        <div class="description">
                            {{ $activated['description'] ?? 'No description available.' }}
                        </div>
                        <div class="meta">
                            <div class="ui small labels">
                                <div class="ui label">
                                    <i class="arrows alternate horizontal icon"></i>
                                    Distance: {{ $activated['distance'] ?? 'Unknown' }}
                                </div>
                                @if(isset($activated['activationStrength']))
                                    <div class="ui blue label">
                                        <i class="lightning icon"></i>
                                        Strength: {{ $activated['activationStrength'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="ui placeholder segment">
            <div class="ui icon header">
                <i class="search icon"></i>
                No activated concepts found
            </div>
            <p>No concepts were activated from "{{ $conceptName }}" with the current parameters.</p>
        </div>
    @endif
    
    <div class="ui divider"></div>
    <a href="/soul/browse/{{ urlencode($conceptName) }}" class="ui button">
        <i class="arrow left icon"></i>
        Back to Concept
    </a>
</div>