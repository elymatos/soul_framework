function objectComponent(object, token) {
    return {
        object: null,
        idDynamicObject: null,
        _token: "",
        bbox: null,
        boxesContainer: null,
        currentFrame: 0,
        tracker: null,
        isTracking: false,
        canCreateBBox: false,
        hasBBoxInCurrentFrame: false,

        init() {
            console.log("Object component init");
            this.annotateObject(object);
            this.idDynamicObject = object.idDynamicObject;
            this.currentFrame = object.startFrame;
            this._token = token;
            this.boxesContainer = document.querySelector("#boxesContainer");
            this.tracker = new ObjectTrackerObject();
            this.tracker.config({
                canvas: drawBoxObject.canvas,
                ctx: drawBoxObject.ctx,
                video: drawBoxObject.video
            });
        },

        async onVideoUpdateState(e) {
            this.currentFrame = e.detail.frame.current;
            this.drawFrameBBox();
            await this.tracking();
        },

        async onBboxDrawn(e) {
            this.bbox = e.detail.bbox;
            // this.object = new DynamicObject();
            console.log("bboxDrawn", this.object);
            // this.object.setDom(this.newBboxElement());
            let bbox = new BoundingBox(this.currentFrame, this.bbox.x, this.bbox.y, this.bbox.width, this.bbox.height, true, null);
            this.object.addBBox(bbox);
            this.interactify(
                this.object,
                async (x, y, width, height, idBoundingBox) => {
                    let bbox = new BoundingBox(this.currentFrame, x, y, width, height, true);
                    this.object.updateBBox(bbox);
                    await ky.post("/annotation/dynamic/updateBBox", {
                        json: {
                            _token: this._token,
                            idBoundingBox: bbox.idBoundingBox,
                            bbox
                        }
                    }).json();
                }
            );
            // console.log(this.idDynamicObject, this.currentFrame, bbox);
            drawBoxObject.disableDrawing();
            bbox.idBoundingBox = await ky.post("/annotation/dynamic/createBBox", {
                json: {
                    _token: this._token,
                    idDynamicObject: this.idDynamicObject,
                    frameNumber: this.currentFrame,
                    bbox//     bbox: bbox
                }
            }).json();
            console.log("bbox created: ", bbox.idBoundingBox);
            this.tracker.getFrameImage(this.currentFrame);
            this.object.drawBoxInFrame(this.currentFrame, "editing");
            this.canCreateBBox = false;
            messenger.notify("success", "New bbox created.");
        },


        annotateObject(object) {
            console.log("annotateObject");
            this.object = new DynamicObject(object);
            this.object.dom = this.newBboxElement();
            this.interactify(
                this.object,
                async (x, y, width, height) => {
                    console.log("inside on change", x, y, width, height);
                    let bbox = new BoundingBox(this.currentFrame, x, y, width, height, true);
                    this.object.updateBBox(bbox);
                    await ky.post("/annotation/dynamic/updateBBox", {
                        json: {
                            _token: this._token,
                            idBoundingBox: bbox.idBoundingBox,
                            bbox
                        }
                    }).json();
                    console.log("end on change");
                }
            );
            let bboxes = object.bboxes;
            for (let j = 0; j < bboxes.length; j++) {
                let bbox = object.bboxes[j];
                let frameNumber = parseInt(bbox.frameNumber);
                let isGroundThrough = true;// parseInt(topLeft.find('l').text()) == 1;
                let x = parseInt(bbox.x);
                let y = parseInt(bbox.y);
                let w = parseInt(bbox.width);
                let h = parseInt(bbox.height);
                let newBBox = new BoundingBox(frameNumber, x, y, w, h, isGroundThrough, parseInt(bbox.idBoundingBox));
                newBBox.blocked = (parseInt(bbox.blocked) === 1);
                this.object.addBBox(newBBox);
            }
            this.canCreateBBox = !this.object.hasBBox();
            console.log("Object annotated");
        },

        createBBox() {
            this.clearFrameObject();
            drawBoxObject.enableDrawing();
            console.log("Drawing mode activated!");
        },

        async toggleTracking() {
            console.log("toogle tracking", this.isTracking ? "tracking" : "stopped");
            if (this.isTracking) {
                this.stopTracking();
            } else {
                if (this.object.getBoundingBoxAt(this.currentFrame)) {
                    await this.startTracking();
                } else {
                    messenger.notify("error", "No bbox found at this frame.");
                }
            }
        },

        async startTracking() {
            document.dispatchEvent(new CustomEvent("tracking-start"));
            this.isTracking = true;
            await this.tracking();
        },

        async tracking() {
            await new Promise(r => setTimeout(r, 800));
            const nextFrame = this.currentFrame + 1;
            // console.log("tracking....", nextFrame,this.object.startFrame, this.object.endFrame);
            if ((this.isTracking) && (nextFrame >= this.object.startFrame) && (nextFrame <= this.object.endFrame)) {
                // console.log("goto Frame ", nextFrame);
                this.gotoFrame(nextFrame);
            } else {
                this.stopTracking();
            }
        },

        stopTracking() {
            //console.log("stop tracking");
            this.isTracking = false;
            document.dispatchEvent(new CustomEvent("tracking-stop"));
            this.object.drawBoxInFrame(this.currentFrame, "editing");
        },


        newBboxElement: () => {
            let dom = document.createElement("div");
            dom.className = "bbox";
            this.boxesContainer.appendChild(dom);
            return dom;
        },

        interactify: (object, onChange) => {
            let dom = object.dom;
            let bbox = $(dom);
            let createHandleDiv = (className, content = null) => {
                //console.log('className = ' + className + '  content = ' + content);
                let handle = document.createElement("div");
                handle.className = className;
                bbox.append(handle);
                if (content !== null) {
                    handle.innerHTML = content;
                }
                return handle;
            };
            let x = createHandleDiv("handle center-drag");
            let i = createHandleDiv("objectId", object.idObject);
            bbox.resizable({
                handles: "n, e, s, w, ne, nw, se, sw",
                onResize: (e) => {
                    var d = e.data;
                    if (d.left < 0) {
                        d.width += d.left;
                        d.left = 0;
                        return false;
                    }
                    if (d.top < 0) {
                        d.height += d.top;
                        d.top = 0;
                        return false;
                    }
                        if (d.left + $(d.target).outerWidth() > $("#canvas").width()) {
                            d.width = $("#canvas").width() - d.left;
                        }
                        if (d.top + $(d.target).outerHeight() > $("#canvas").height()) {
                            d.height = $("#canvas").height() - d.top;
                        }
                },
                onStopResize: (e) => {
                    bbox.css("display", "none");
                    var d = e.data;
                    if (d.left < 0) {
                        d.width += d.left;
                        d.left = 0;
                    }
                    if (d.top < 0) {
                        d.height += d.top;
                        d.top = 0;
                    }
                    if (d.left + $(d.target).outerWidth() > $("#canvas").width()) {
                        d.width = $("#canvas").width() - d.left;
                    }
                    if (d.top + $(d.target).outerHeight() > $("#canvas").height()) {
                        d.height = $("#canvas").height() - d.top;
                    }
                    bbox.css({
                        'top': d.top + 'px',
                        'left': d.left + 'px',
                        'width': d.width + 'px',
                        'height': d.height + 'px',
                    });
                    bbox.css("display", "block");
                    onChange(Math.round(d.left), Math.round(d.top), Math.round(d.width), Math.round(d.height));
                }
            });
            bbox.draggable({
                handle: $(x),
                onDrag: (e) => {
                    var d = e.data;
                    if (d.left < 0) {
                        d.left = 0;
                    }
                    if (d.top < 0) {
                        d.top = 0;
                    }
                    if (d.left + $(d.target).outerWidth() > $("#canvas").width()) {
                        d.left = $("#canvas").width() - $(d.target).outerWidth();
                    }
                    if (d.top + $(d.target).outerHeight() > $("#canvas").height()) {
                        d.top = $("#canvas").height() - $(d.target).outerHeight();
                    }
                },
                onStopDrag: (e) => {
                    let position = bbox.position();
                    onChange(Math.round(position.left), Math.round(position.top), Math.round(bbox.outerWidth()), Math.round(bbox.outerHeight()));
                }
            });
            bbox.css("display", "none");
        },

        clearFrameObject: function() {
            $(".bbox").css("display", "none");
        },

        gotoFrame(frameNumber) {
            document.dispatchEvent(new CustomEvent("video-seek-frame", {
                detail: {
                    frameNumber
                }
            }));
        },

        async drawFrameBBox() {
            if (this.object) {
                this.clearFrameObject();
                if (this.isTracking) {
                    // se está tracking, a box:
                    // - ou já existe (foi criada antes)
                    // - ou precisa ser criada
                    let bbox = this.object.getBoundingBoxAt(this.currentFrame);
                    if (bbox === null) {
                        await this.tracker.setBBoxForObject(this.object, this.currentFrame);
                        let bbox = this.object.getBoundingBoxAt(this.currentFrame);
                        // console.log("creating bbox at frame", this.currentFrame);
                        bbox.idBoundingBox = await ky.post("/annotation/dynamicMode/createBBox", {
                            json: {
                                _token: this._token,
                                idDynamicObject: this.idDynamicObject,
                                frameNumber: this.currentFrame,
                                bbox
                            }
                        }).json();
                    }
                }
                // let x = this.object.getBoundingBoxAt(this.currentFrame);
                // console.log("drawFrameBBox", this.currentFrame,x ? 'ok' : 'nine');
                this.object.drawBoxInFrame(this.currentFrame, this.isTracking ? "tracking" : "editing");
            }
        }
    };
}
