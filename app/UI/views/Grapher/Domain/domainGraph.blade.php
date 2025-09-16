<script>
    $(function () {

        var grapher = new Grapher({
            nodes: {{ Js::from($graph['nodes']) }},
            links: {{ Js::from($graph['links']) }},
            buildGraph: function () {
                var elements = [];
                var links = [];
                let rect, text, w;
                let connector = $('#connector').val();
                for (var index in this.nodes) {
                    let node = this.nodes[index]
                    if (node['type'] === 'frame') {
                        rect = new joint.shapes.standard.Rectangle({
                            id: index,
                            z: 2,
                        });
                        text = node['name'];
                        w = Math.max(text.length * 8, 100);
                        rect.resize(w, 16);
                        rect.attr({
                            body: {
                                class: `color_${node['type']}`,
                            },
                            label: {
                                class: `color_${node['type']}--text`,
                                text: text,
                            }
                        });
                        rect.resize(w, 20);
                    }
                    if (node['type'] === 'fe') {
                        text = node['name'];
                        //w = Math.max(text.length * 8, 100);
                        w = (text.length * 8) + 20;
                        rect = new joint.shapes.standard.Rectangle({
                            id: index,
                            z: 2,
                            attrs: {
                                body: {},
                                foreignObject: {
                                    width: w,
                                    height: 16,
                                    // class: `fe color_${node['idColor']}`,
                                    class: `fe`,
                                },
                                fespanicon: {
                                    class: node['icon']
                                },
                                fespanname: {
                                    class: `color_${node['idColor']}`,
                                }
                            },
                            markup: [{
                                tagName: 'foreignObject',
                                selector: 'foreignObject',
                                children: [
                                    {
                                        namespaceURI: 'http://www.w3.org/1999/xhtml',
                                        tagName: 'span',
                                        selector: 'fespanicon',
                                    }, {
                                        namespaceURI: 'http://www.w3.org/1999/xhtml',
                                        tagName: 'span',
                                        selector: 'fespanname',
                                        textContent: text
                                    }
                                ]
                            }]
                        });
                        rect.resize(w, 20);
                    }
                    elements.push(rect);
                }

                for (var source in this.links) {
                    for (var target in this.links[source]) {
                        let relation = this.links[source][target]
                        link = new joint.shapes.standard.Link({
                            source: {id: source},
                            target: {id: target},
                            attrs: {
                                line: {
                                    class: `color_${relation['relationEntry']}`,
                                    strokeWidth: 1.5,
                                    targetMarker: {
                                        'class': `color_${relation['relationEntry']}--marker`,
                                        'type': 'path',
                                        'd': 'M 8 -2 0 0 8 2 z'
                                    }
                                }
                            }
                        });
                        link.connector(connector);
                        link.prop({
                            data: {
                                id: relation['idEntityRelation'],
                                type: relation['type'],
                            }
                        })
                        links.push(link)
                    }
                }
                return elements.concat(links);
            },
            cellDblClick: function (cellView) {
                var currentElement = cellView.model;
                if (cellView.model.isElement()) {
                    htmx.ajax('POST', '/grapher/domain/graph/' + currentElement.id, {target: '#graph'})
                } else if (cellView.model.isLink()) {
                    console.log(currentElement.source(), currentElement.target());
                }
            },
            cellClick: function (cellView) {
                var currentElement = cellView.model;
                if (cellView.model.isElement()) {
                    htmx.ajax("GET","/grapher/frame/report/" + currentElement.id, {target: '#frameReport'});
                    $('#graph-report').flyout('toggle');
                }
            },
            linkEnter: function (linkView) {
                let infoButton;
                let data = linkView.model.prop('data');
                if (data.type === 'ff') {
                    infoButton = new joint.linkTools.Button({
                        markup: [{
                            tagName: 'image',
                            attributes: {
                                'href': '/images/reorder.svg',
                                'x': -12,
                                'y': -12
                            }
                        }],
                        distance: '50%',
                        offset: 0,
                        action: function (evt) {
                            htmx.ajax('POST', `/grapher/framefe/graph/${data.id}`, {target: '#graph'})
                        }
                    });
                } else {
                    infoButton = null;
                }
                var verticesTool = new joint.linkTools.Vertices();
                var toolsView = new joint.dia.ToolsView({
                    tools: [
                        verticesTool, infoButton
                    ]
                });
                linkView.addTools(toolsView)
                linkView.showTools()
            },
            elementEnter: function (elementView) {
                var removeButton = new joint.elementTools.Remove({});
                var toolsView = new joint.dia.ToolsView({
                    tools: [
                        removeButton
                    ]
                });
                elementView.addTools(toolsView)
                elementView.showTools()
            }
        });
        grapher.layout();
    });
</script>
