<!-- AlpineJS Tree Component -->
<div class="tree-container"
     x-data="treeComponent()"
     x-init="init()"
>
    <!-- Header -->
    @if($title != '')
        <div class="tree-header">{{$title}}</div>
    @endif

    <!-- Tree Body -->
    <div class="tree-body">
        <table class="tree-table">
            <tbody>
            @foreach($data as $item)
                @php($idNode = $item['type'] . '_' . $item['id'])
                <tr
                    class="row-data"
                >
                    @if(!($item['leaf'] ?? false))
                        <!-- Toggle Cell -->
                        <td class="toggle"
                            @click="toggleNode('{{$idNode}}')"
                        >
                            <span class="toggle-icon"
                                  :class="expandedNodes['{{$idNode}}'] ? 'expanded' : 'collapsed'"
                            >
                            </span>
                        </td>
                    @else
                        <td></td>
                    @endif

                    <!-- Content Cell -->
                    @if(isset($item['formatedId']))
                        <td class="content-cell">
                            <span class="tree-item-text"
                                  @click="selectItem({{$item['id']}},'{{$item['type']}}')"
                            >
                                {!! $item['formatedId'] !!}
                            </span>
                        </td>
                    @endif
                    @if(isset($item['extra']))
                        <td class="content-cell">
                            <span class="tree-item-text"
                                  @click="selectItem({{$item['id']}},'{{$item['type']}}')"
                            >
                                {!! $item['extra'] !!}
                            </span>
                        </td>
                    @endif
                    <td class="content-cell">
                            <span class="tree-item-text"
                                  @click="selectItem({{$item['id']}},'{{$item['type']}}')"
                            >
                                {!! $item['text'] !!}
                            </span>
                    </td>
                </tr>
                <tr
                    id="row_{{$idNode}}"
                    :class="expandedNodes['{{$idNode}}'] ? '' : 'hidden'"
                >
                    <td></td>
                    <td>
                        <!-- Tree Content Container -->
                        <div id="tree_{{$idNode}}"
                             class="tree-content"
                             :class="{ 'hidden': !expandedNodes['{{$idNode}}'] }"
                             x-show="expandedNodes['{{$idNode}}']"
                             x-transition>

                            <!-- Loading indicator -->
                            <div x-show="loadingNodes[{{$item['id']}}]" class="loading">
                                <div class="ui segment border-none">
                                    <div class="ui active inverted dimmer">
                                        <div class="ui text loader">Loading</div>
                                    </div>
                                    <p></p>
                                </div>
                            </div>

                            <!-- HTMX will populate this area -->
                            <div hx-post="{{$url}}"
                                 hx-vals='{"type": "{{$item['type']}}", "id" : "{{$item['id']}}"}'
                                 hx-target="#tree_{{$idNode}}"
                                 hx-swap="innerHTML"
                                 hx-trigger="load-{{$idNode}} from:body"
                                 @htmx:before-request="loadingNodes['{{$idNode}}'] = true"
                                 @htmx:after-request="loadingNodes['{{$idNode}}'] = false; processLoadedContent($event.target)">
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
