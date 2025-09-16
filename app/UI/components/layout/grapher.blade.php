<x-layout.index>
    @include('components.layout.head')
    <header id="header">
        <i id="headMenuIcon" class="sidebar icon menuIcon cursor-pointer"></i>
        {{$head}}
    </header>
    <div id="content">
        <div class="contentContainer ui pushable">
            @include('Grapher.report')
            @include('Grapher.controls')
            <div class="menuLeft ui left vertical menu sidebar">
                @include("components.layout.menu")
            </div>
            <div class="pusher closing pusher-full">
                <main role="main" class="main">
                    <div id="graphPane"
                         class="flex flex-column w-full h-full p-0 wt-layout-grapher">
                        {{$main}}
                        <div
                            id="graph"
                        >
                        </div>
                        <div id="paper"></div>
                    </div>
                    <wt-go-top id="btnTop" label="Top" offset="64"></wt-go-top>
                </main>
            </div>
        </div>
    </div>
    <footer>
        <div class="flex justify-content-between w-full">
            <div>
                @include("components.layout.footer")
            </div>
            <div>
                {!! config('webtool.version') !!}
            </div>
        </div>
    </footer>
    <script>
        $(".menuLeft")
            .sidebar({
                context: $(".contentContainer"),
                dimPage: false,
                transition: "overlay",
                mobileTransition: "overlay",
                closable: false,
            })
            .sidebar("attach events", ".menuIcon")
            .sidebar("hide")
        ;
        $(function() {
            $("#graph-drawer").flyout({
                context: $('.main')
            });
            $("#graph-report").flyout({
                context: $('.main')
            });
            window.Grapher = joint.mvc.View.extend({
                options: {
                    nodes: [],
                    links: [],
                    cells: [],
                    padding: 50,
                    el: document.getElementById("layout-controls"),
                    paper: null,
                    panAndZoom: null,
                    buildGraph: function() {
                        return [];
                    },
                    cellDblClick: function(cellView) {
                    },
                    linkEnter: function(linkView) {
                    },
                    elementEnter: function(elementView) {
                    }
                },
                init: function() {
                    let that = this;
                    let options = this.options;

                    this.paper = new joint.dia.Paper({
                        el: document.getElementById("paper"),
                        width: "calc(100% - 16px)",
                        height: "calc(100% - 16px - 30px - 50px)",
                        sorting: joint.dia.Paper.sorting.APPROX
                    });

                    this.paper.on("blank:pointerdown", (evt, x, y) => {
                        this.panAndZoom.enablePan();
                    });

                    this.paper.on("cell:pointerup blank:pointerup", (cellView, event) => {
                        this.panAndZoom.disablePan();
                    });

                    this.paper.on("cell:pointerdblclick", options.cellDblClick);

                    this.paper.on("cell:pointerclick", options.cellClick);

                    this.paper.on("link:mouseenter", options.linkEnter);

                    this.paper.on("link:mouseleave", function(linkView) {
                        linkView.removeTools();
                    });

                    this.paper.on("element:mouseenter", options.elementEnter);

                    this.paper.on("element:mouseleave", function(elementView) {
                        elementView.removeTools();
                    });

                    $("#ranker_dropdown").dropdown({
                        onChange: function(value) {
                            $("#ranker").val(value);
                            that.onChange();
                        }
                    });
                    $("#rankdir_dropdown").dropdown({
                        onChange: function(value) {
                            $("#rankdir").val(value);
                            that.onChange();
                        }
                    });
                    $("#align_dropdown").dropdown({
                        onChange: function(value) {
                            $("#align").val(value);
                            that.onChange();
                        }
                    });

                    $("#vertices").on("change", () => this.onChange());
                    $("#ranksep").on("change", () => this.onChange());
                    $("#nodesep").on("change", () => this.onChange());
                    $("#edgesep").on("change", () => this.onChange());

                    $("#connector_dropdown").dropdown({
                        onChange: (value) => {
                            $("#connector").val(value);
                            let links = this.paper.model.getLinks();
                            let connector = value;
                            for (var link of links) {
                                link.connector(connector);
                                this.paper.findViewByModel(link.id).render();
                            }
                        }
                    });
                    this.cells = options.buildGraph();
                },
                onChange: function() {
                    console.log($("#ranker").val(), $("#rankdir").val(), $("#align").val(), $("#connector").val());
                    this.layout();
                    this.trigger("layout");
                },
                layout: function() {
                    let paper = this.paper;
                    let graph = paper.model;
                    let cells = this.cells;
                    paper.freeze();
                    var layoutOptions = this.getLayoutOptions();
                    joint.layout.DirectedGraph.layout(cells, layoutOptions);
                    if (graph.getCells().length === 0) {
                        // The graph could be empty at the beginning to avoid cells rendering
                        // and their subsequent update when elements are translated
                        graph.resetCells(cells);
                    }
                    // let x = paper.fitToContent({
                    //     padding: 0,
                    //     allowNewOrigin: 'any',
                    //     useModelGeometry: true
                    // });
                    // console.log(x);
                    paper.unfreeze();
                    let h1 = $("#paper").innerHeight();
                    let w1 = $("#paper").innerWidth();
                    let b = paper.getContentBBox();
                    let h2 = b.height;
                    let w2 = b.width;
                    if (w2 > w1) {
                        paper.scale((w1 / w2));
                    } else if (h2 > h1) {
                        paper.scale((h1 / h2));
                    }

                    this.panAndZoom = window.svgPanZoom(this.paper.svg, {
                        zoomEnabled: true,
                        controlIconsEnabled: true,
                        dblClickZoomEnabled: false,
                        fit: false,
                        center: false
                    });

                    this.panAndZoom.enableControlIcons();
                    this.panAndZoom.disablePan();
                },
                getLayoutOptions: function() {
                    return {
                        dagre: dagre,
                        graphlib: dagre.graphlib,
                        setVertices: $("#vertices").is(":checked") ? true : function(link) {
                            link.set("vertices", []);
                        },
                        setLinkVertices: $("#vertices").is(":checked"),
                        setLabels: true,
                        ranker: $("#ranker").val(),
                        rankDir: $("#rankdir").val(),
                        align: $("#align").val(),
                        rankSep: parseInt($("#ranksep").val(), 10),
                        edgeSep: parseInt($("#edgesep").val(), 10),
                        nodeSep: parseInt($("#nodesep").val(), 10)
                    };
                }
            });


        });
    </script>
</x-layout.index>
