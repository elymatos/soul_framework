<div class="ui breadcrumb page-breadcrumb">
    @foreach($sections as $section)
        @if ($loop->last)
            <div class="active section">{{$section[1]}}</div>
        @else
            <a href="{{$section[0]}}" class="section">{{$section[1]}}</a>
            <span class="divider">/</span>
        @endif
    @endforeach
</div>
