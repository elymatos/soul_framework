function boxesComponent(idVideoDOMElement, object) {
    return {

        idVideoDOMElement: "",
        boxesContainer: null,
        canvas: null,
        ctx: null,
        video: null,
        bgColor: "#ffff00",
        fgColor: "#000",
        offsetX: 0,
        offsetY: 0,
        startX: 0,
        startY: 0,
        mouseX: 0,
        mouseY: 0,
        isDown: false,
        box: {
            x: 0,
            y: 0,
            width: 0,
            height: 0
        },
        previousBBox: null,
        bbox: null,
        object: null,
        currentFrame: 0,
        startFrame: 0,
        endFrame: 0,
        tracker: null,
        isTracking: false,
        hasBBox: false,
        dom: null,
        _token: "",
        currentBBox: null,
        bboxes:{},


        async init() {
            console.log("Boxes component init");
            this._token = $("#_token").val();

            this.idVideoDOMElement = idVideoDOMElement;
            this.object = object;
            console.log(this.object);

            this.canvas = document.getElementById("canvas");
            if (!this.canvas) {
                console.error("Canvas element with ID 'canvas' not found.");
                return;
            }
            this.ctx = this.canvas.getContext("2d", {
                willReadFrequently: true
            });

            this.video = document.getElementById(this.idVideoDOMElement);
            if (this.video) {
                const rect = this.video.getBoundingClientRect();
                this.offsetX = rect.x;
                this.offsetY = rect.y;
            } else {
                console.warn(`Video element with ID '${this.idVideoDOMElement}' not found. Offset will be based on canvas position.`);
                const canvasRect = this.canvas.getBoundingClientRect();
                this.offsetX = canvasRect.left;
                this.offsetY = canvasRect.top;
            }

            this.startFrame = this.object.startFrame;
            this.endFrame = this.object.endFrame;
            this.currentFrame = this.object.startFrame;

            this.tracker = new ObjectTrackerObject();
            this.tracker.config({
                canvas: this.canvas,
                ctx: this.ctx,
                video: this.video
            });
            document.dispatchEvent(new CustomEvent("video-seek-frame", {
                detail: {
                    frameNumber: this.object.startFrame
                }
            }));
        },

        async onVideoUpdateState(e) {
            this.clearBBox();
            this.currentFrame = e.detail.frame.current;
            console.log("current frame", this.currentFrame, this.startFrame, this.endFrame);
            if ((this.currentFrame >= this.startFrame) && (this.currentFrame <= this.endFrame)) {
                await this.showBBox();
                await this.tracking();
            } else {
                document.dispatchEvent(new CustomEvent("bbox-drawn", {
                    detail: {
                        bbox: null
                    }
                }));
            }
        },

        async onBBoxToggleTracking() {
            this.isTracking = !this.isTracking;
        },

        async onStartTracking() {
            console.log("bbox onStartTracking");
            this.isTracking = true;
            await this.tracking();
        },

        onStopTracking() {
            //console.log("stop tracking");
            console.log("bbox onStopTracking");
            this.isTracking = false;
            //this.object.drawBoxInFrame(this.currentFrame, "editing");
        },

        async onBBoxCreated(e) {
            this.bbox = e.detail.bbox;
            console.log("bbox created object", this.object);
            let bbox = new BoundingBox(this.currentFrame, this.bbox.x, this.bbox.y, this.bbox.width, this.bbox.height, true, false);
            this.disableDrawing();
            bbox.idBoundingBox = await ky.post("/annotation/dynamic/createBBox", {
                json: {
                    _token: this._token,
                    idDynamicObject: this.object.idDynamicObject,
                    frameNumber: this.currentFrame,
                    bbox//     bbox: bbox
                }
            }).json();
            console.log("bbox created id ", bbox.idBoundingBox);
            this.tracker.getFrameImage(this.currentFrame);
            this.showBBox();
            // document.dispatchEvent(new CustomEvent("bbox-created", {
            //     detail: {
            //         idDynamicObject: this.object.idDynamicObject,
            //     }
            // }));
            messenger.notify("success", "New bbox created.");
        },

        async onBBoxChange(bbox) {
            console.log("on bbox change ", bbox);
            // this.object.updateBBox(bbox);
            await ky.post("/annotation/dynamic/updateBBox", {
                json: {
                    _token: this._token,
                    idBoundingBox: bbox.idBoundingBox,
                    bbox
                }
            }).json();
        },

        async onBBoxChangeBlocked(e) {
            let bbox = this.currentBBox;
            bbox.blocked = e.target.classList.contains('checked') ? 1 : 0;
            console.log("on bbox change blocked ", this.currentBBox,bbox.blocked);
            await ky.post("/annotation/dynamic/updateBBox", {
                json: {
                    _token: this._token,
                    idBoundingBox: bbox.idBoundingBox,
                    bbox
                }
            }).json();
            this.showBBox();
        },

        enableDrawing() {
            this.isDown = false;
            // Bind 'this' context for all event listeners
            this.boundHandleMouseDown = this.handleMouseDown.bind(this);
            this.boundHandleMouseUp = this.handleMouseUp.bind(this);
            this.boundHandleMouseOut = this.handleMouseOut.bind(this);
            this.boundHandleMouseMove = this.handleMouseMove.bind(this);

            this.canvas.addEventListener("mousedown", this.boundHandleMouseDown);
            this.canvas.addEventListener("mouseup", this.boundHandleMouseUp);
            this.canvas.addEventListener("mouseout", this.boundHandleMouseOut);
            this.canvas.addEventListener("mousemove", this.boundHandleMouseMove);
            console.log("Drawing event listeners enabled.");
        },

        disableDrawing() {
            this.isDown = false;
            // Use the same bound functions to remove listeners
            if (this.boundHandleMouseDown) {
                this.canvas.removeEventListener("mousedown", this.boundHandleMouseDown);
                this.canvas.removeEventListener("mouseup", this.boundHandleMouseUp);
                this.canvas.removeEventListener("mouseout", this.boundHandleMouseOut);
                this.canvas.removeEventListener("mousemove", this.boundHandleMouseMove);
            }
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height); // Clear canvas on disable
            console.log("Drawing event listeners disabled and canvas cleared.");
        },

        async getCurrentBBox() {
            let bbox = await ky.get("/annotation/dynamic/getBBox", {
                searchParams: {
                    idDynamicObject: this.object.idDynamicObject,
                    frameNumber: this.currentFrame,
                    isTracking: this.isTracking ? 1 : 0
                }
            }).json();
            const { idBoundingBox, frameNumber, frameTime, x, y, width, height, blocked} = bbox;
            this.currentBBox = { idBoundingBox, frameNumber, frameTime, x, y, width, height, blocked };
            return bbox;
        },

        async showBBox() {
            let bbox = await this.getCurrentBBox();
            if (bbox) {
                this.drawBBox(bbox);
                this.bboxes[this.currentFrame] = bbox;
                document.dispatchEvent(new CustomEvent("bbox-drawn", {
                    detail: {
                        bbox,
                    }
                }));
            } else {
                let previousBBox = this.bboxes[this.currentFrame - 1];
                if (previousBBox) {
                    console.log("create new bbox via tracking on frame", this.currentFrame, previousBBox);
                    bbox = await this.tracker.trackBBox(this.currentFrame, previousBBox);
                    console.log("generated bbox", bbox);
                    bbox.blocked = previousBBox.blocked;
                    bbox.idBoundingBox = await ky.post("/annotation/dynamic/createBBox", {
                        json: {
                            _token: this._token,
                            idDynamicObject: this.object.idDynamicObject,
                            frameNumber: this.currentFrame,
                            bbox
                        }
                    }).json();
                    this.showBBox();
                    // this.drawBBox(bbox);
                    // this.bboxes[this.currentFrame] = bbox;
                }
            }


            // await htmx.ajax("GET", "/annotation/dynamic/getBBoxView", {
            //     values: {
            //         idDynamicObject: this.object.idDynamicObject,
            //         frameNumber: this.currentFrame,
            //         isTracking: this.isTracking
            //     },
            //     target: "#boxesContainer",
            //     swap: "innerHTML"
            // });
        },

        // storeCurrentBBox(frameNumber, rawBBox) {
        //     console.log(rawBBox);
        //     this.bboxes[frameNumber] = new BoundingBox(
        //         rawBBox.frameNumber,
        //         rawBBox.x,
        //         rawBBox.y,
        //         rawBBox.width,
        //         rawBBox.height,
        //         true,
        //         rawBBox.blocked,
        //         rawBBox.idBoundingBox
        //     );
        //     console.log(this.bboxes);
        //
        // },

        // async createNewBBoxViaTracking(frameNumber) {
        //     let previousBBox = this.bboxes[frameNumber - 1];
        //     if (previousBBox) {
        //         console.log("create new bbox via tracking on frame", frameNumber, previousBBox);
        //         bbox = await this.tracker.trackBBox(frameNumber, previousBBox);
        //         bbox.blocked = previousBBox.blocked;
        //         bbox.idBoundingBox = await ky.post("/annotation/dynamic/createBBox", {
        //             json: {
        //                 _token: this._token,
        //                 idDynamicObject: this.object.idDynamicObject,
        //                 frameNumber,
        //                 bbox
        //             }
        //         }).json();
        //         await this.showBBox();
        //     }
        // },

        initializeBBox: (idDynamicObject, rawBBox) => {
            // this.bbox = new BoundingBox(
            //     rawBBox.frameNumber,
            //     rawBBox.x,
            //     rawBBox.y,
            //     rawBBox.width,
            //     rawBBox.height,
            //     true,
            //     rawBBox.blocked,
            //     rawBBox.idBoundingBox
            // );
            // let objectidElement = $('.objectId');
            // objectidElement.innerHTML = idDynamicObject;
            // this.drawBox(this.bbox);
            // console.log("initialized BBox", this.bbox);
        },

        drawFrameBBox: async () => {
            // if (this.hasBBox) {
            // this.clearFrameObject();
            // let bbox = this.bbox;
            // if (this.isTracking) {
            //     // se está tracking, a box:
            //     // - ou já existe (foi criada antes)
            //     // - ou precisa ser criada
            //     // let bbox = this.object.getBoundingBoxAt(this.currentFrame);
            //     // if (bbox === null) {
            //     if (!this.hasBBox) {
            //         bbox = await this.tracker.trackBBox(this.currentFrame, this.previousBBox);
            //         // let bbox = this.object.getBoundingBoxAt(this.currentFrame);
            //         bbox.blocked = this.previousBBox.blocked;
            //         // console.log("creating bbox at frame", this.currentFrame);
            //         bbox.idBoundingBox = await ky.post("/annotation/dynamic/createBBox", {
            //             json: {
            //                 _token: this._token,
            //                 idDynamicObject: this.idDynamicObject,
            //                 frameNumber: this.currentFrame,
            //                 bbox
            //             }
            //         }).json();
            //     }
            // }
            // this.initializeBBox(bbox);
            // let x = this.object.getBoundingBoxAt(this.currentFrame);
            // console.log("drawFrameBBox", this.currentFrame,x ? 'ok' : 'nine');
            // console.log(this.bbox);
            // this.drawBoxInFrame(this.bbox, this.currentFrame, this.isTracking ? "tracking" : "editing");
            // }
        },

        onBBoxCreate() {
            this.clearBBox();
            this.enableDrawing();
            console.log("Drawing mode activated!");
        },

        async tracking() {
            await new Promise(r => setTimeout(r, 800));
            const nextFrame = this.currentFrame + 1;
            console.log("tracking....", nextFrame,this.object.startFrame, this.object.endFrame);
            if ((this.isTracking) && (nextFrame >= this.startFrame) && (nextFrame <= this.endFrame)) {
                // console.log("goto Frame ", nextFrame);
                //this.previousBBox = JSON.parse(JSON.stringify(this.bbox));
                this.gotoFrame(nextFrame);
            } else {
                this.onStopTracking();
            }
        },


        // newBboxElement: (onChange) => {
        //     this.dom = document.createElement("div");
        //     this.dom.className = "bbox";
        //     this.dom.dataset.blocked = false;
        //     this.boxesContainer.appendChild(this.dom);
        //     //let dom = object.dom;
        //     let bbox = $(this.dom);
        //     let containerHeight = $("#boxesContainer").height();
        //     let containerWidth = $("#boxesContainer").width();
        //     let drag = document.createElement("div");
        //     drag.className = "handle center-drag";
        //     bbox.append(drag);
        //     let objectId = document.createElement("div");
        //     objectId.className = "objectId";
        //     bbox.append(objectId);
        //     bbox.resizable({
        //         handles: "n, e, s, w, ne, nw, se, sw",
        //         onResize: (e) => {
        //             let d = e.data;
        //             if (d.left < 0) {
        //                 d.width += d.left;
        //                 d.left = 0;
        //                 return false;
        //             }
        //             if (d.top < 0) {
        //                 d.height += d.top;
        //                 d.top = 0;
        //                 return false;
        //             }
        //             if (d.left + $(d.target).outerWidth() > containerWidth) {
        //                 d.width = containerWidth - d.left;
        //             }
        //             if (d.top + $(d.target).outerHeight() > containerHeight) {
        //                 d.height = containerHeight - d.top;
        //             }
        //         },
        //         onStopResize: (e) => {
        //             bbox.css("display", "none");
        //             let d = e.data;
        //             if (d.left < 0) {
        //                 d.width += d.left;
        //                 d.left = 0;
        //             }
        //             if (d.top < 0) {
        //                 d.height += d.top;
        //                 d.top = 0;
        //             }
        //             if (d.left + $(d.target).outerWidth() > containerWidth) {
        //                 d.width = containerWidth - d.left;
        //             }
        //             if (d.top + $(d.target).outerHeight() > containerHeight) {
        //                 d.height = containerHeight - d.top;
        //             }
        //             bbox.css({
        //                 "top": d.top + "px",
        //                 "left": d.left + "px",
        //                 "width": d.width + "px",
        //                 "height": d.height + "px"
        //             });
        //             bbox.css("display", "block");
        //             let bboxChanged = new BoundingBox(
        //                 this.currentFrame,
        //                 Math.round(d.left),
        //                 Math.round(d.top),
        //                 Math.round(d.width),
        //                 Math.round(d.height),
        //                 true,
        //                 dom.dataset.blocked
        //             );
        //             onChange(bboxChanged);
        //         }
        //     });
        //     bbox.draggable({
        //         // handle: $(x),
        //         onDrag: (e) => {
        //             var d = e.data;
        //             if (d.left < 0) {
        //                 d.left = 0;
        //             }
        //             if (d.top < 0) {
        //                 d.top = 0;
        //             }
        //             if (d.left + $(d.target).outerWidth() > containerWidth) {
        //                 d.left = containerWidth - $(d.target).outerWidth();
        //             }
        //             if (d.top + $(d.target).outerHeight() > containerHeight) {
        //                 d.top = containerHeight - $(d.target).outerHeight();
        //             }
        //         },
        //         onStopDrag: (e) => {
        //             let position = bbox.position();
        //             let bboxChanged = new BoundingBox(
        //                 this.currentFrame,
        //                 Math.round(position.left),
        //                 Math.round(position.top),
        //                 Math.round(bbox.outerWidth()),
        //                 Math.round(bbox.outerHeight()),
        //                 true,
        //                 dom.dataset.blocked
        //             );
        //             onChange(bboxChanged);
        //         }
        //     });
        //     bbox.css("display", "none");
        //     console.log("new BBox element created", this.dom);
        // },

        clearBBox: function() {
            $(".bbox").css("display", "none");
        },

        gotoFrame(frameNumber) {
            document.dispatchEvent(new CustomEvent("video-seek-frame", {
                detail: {
                    frameNumber
                }
            }));
        },

        drawBBox(bbox) {
            let $dom = $(".bbox");
            // console.log("drawBBox", bbox, $dom, this.bgColor);
            $dom.css("display", "none");
            if (bbox) {
                if (!this.hidden) {
                    $dom.css({
                        position: "absolute",
                        display: "block",
                        width: bbox.width + "px",
                        height: bbox.height + "px",
                        left: bbox.x + "px",
                        top: bbox.y + "px",
                        borderColor: this.bgColor,
                        backgroundColor: "transparent",
                        opacity: 1
                    });

                    $dom.find(".objectId").css({
                        backgroundColor: this.bgColor,
                        color: this.fgColor
                    });

                    if (this.isTracking) {
                        $dom.css({
                            borderStyle: "dotted",
                            borderWidth: "2px"
                        });
                    } else {
                        $dom.css({
                            borderStyle: "solid",
                            borderWidth: "4px"
                        });
                    }
                    this.visible = true;
                    if (bbox.blocked) {
                        $dom.css({
                            borderStyle: "dashed",
                            backgroundColor: "white",
                            opacity: 0.4
                        });
                    }
                    $dom.css("display", "block");
                }
            }
        },


        handleMouseDown(e) {
            e.preventDefault();
            e.stopPropagation();

            this.startX = parseInt(e.clientX - this.offsetX);
            this.startY = parseInt(e.clientY - this.offsetY);
            this.isDown = true;
        },

        handleMouseUp(e) {
            e.preventDefault();
            e.stopPropagation();
            this.isDown = false;

            // Clear the canvas. This is temporary feedback, the final box will be managed elsewhere.
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            // Check if a valid box was drawn (i.e., not just a click)
            // You might want to consider Math.abs(this.box.width) and Math.abs(this.box.height)
            // if users can drag in any direction and you always want positive dimensions.
            if (this.box.width !== 0 && this.box.height !== 0) {
                console.log("Box Finalized:", this.box);

                // Dispatch the custom event, using 'this.box' directly
                document.dispatchEvent(new CustomEvent("bbox-created", {
                    detail: {
                        bbox: { // Recreate the bbox object with absolute values for consistency
                            x: Math.min(this.startX, this.mouseX), // Take the smaller X for the top-left
                            y: Math.min(this.startY, this.mouseY), // Take the smaller Y for the top-left
                            width: Math.abs(this.mouseX - this.startX), // Absolute width
                            height: Math.abs(this.mouseY - this.startY) // Absolute height
                        }
                    }
                }));
            }
        },

        handleMouseOut(e) {
            e.preventDefault();
            e.stopPropagation();
            this.isDown = false;
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        },

        drawCrosshairs(x, y) {
            this.ctx.strokeStyle = this.bgColor;
            this.ctx.lineWidth = 1;

            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, this.canvas.height);
            this.ctx.stroke();

            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(this.canvas.width, y);
            this.ctx.stroke();
        },

        handleMouseMove(e) {
            e.preventDefault();
            e.stopPropagation();

            this.mouseX = parseInt(e.clientX - this.offsetX);
            this.mouseY = parseInt(e.clientY - this.offsetY);

            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            this.drawCrosshairs(this.mouseX, this.mouseY);

            if (!this.isDown) {
                return;
            }

            const width = this.mouseX - this.startX;
            const height = this.mouseY - this.startY;

            this.ctx.strokeStyle = this.bgColor;
            this.ctx.strokeRect(this.startX, this.startY, width, height);

            this.box = {
                x: this.startX,
                y: this.startY,
                width: width,
                height: height
            };
        }


    };
}
