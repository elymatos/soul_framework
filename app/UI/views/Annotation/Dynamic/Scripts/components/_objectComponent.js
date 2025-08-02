function _objectComponent(object) {
    return {
        object: null,
        idDynamicObject: null,
        currentFrame: 0,
        isTracking: false,

        async init() {
            console.log("Object component init");
            this.object = object;
            console.log(this.object);
            this.idDynamicObject = object.idDynamicObject;
            this.currentFrame = object.startFrame;
            // htmx.ajax("GET", "/annotation/dynamic/getBoxesContainer/" + this.idDynamicObject, {
            //     target: "#boxes",
            //     swap: "innerHTML"
            // }).then(() => {
            //     console.log("## dispatching video-seek-frame",this.object.startFrame);
            //     document.dispatchEvent(new CustomEvent("video-seek-frame", {
            //         detail: {
            //             frameNumber: this.object.startFrame
            //         }
            //     }));
            // });
        },

        async onVideoUpdateState(e) {
            this.currentFrame = e.detail.frame.current;
        },

        async onBBoxCreated(e) {
            if (e.detail.idDynamicObject === this.object.idDynamicObject) {
                this.object.hasBBoxes = true;
            }
        },

        get canCreateBBox() {
            console.log("h",this.object.hasBBoxes);
            return (!this.object.hasBBoxes) && (this.currentFrame === this.object.startFrame);
        },

        get canTrack() {
            return (this.object.hasBBoxes) && (!this.canCreateBBox);
        },

        // async onBBoxBlocked() {
        //     console.log($('.bbox'));
        //     let blocked = $('.bbox').data('blocked');
        //     console.log("on bbox blocked", blocked);
        //     let bbox = this.object.getBoundingBoxAt(this.currentFrame);
        //     bbox.blocked = !blocked;
        //     await this.onBBoxChange(bbox);
        //     this.object.drawBoxInFrame(this.currentFrame, this.isTracking ? "tracking" : "editing");
        //     $('.bbox').data('blocked', bbox.blocked);
        //
        //     // console.log("blocked", this.object.blocked);
        //     // this.object.blocked = !this.object.blocked;
        //     // let bbox = this.object.getBoundingBoxAt(this.currentFrame);
        //     // console.log(bbox);
        //     // bbox.blocked = this.object.blocked;
        //     // this.object.updateBBox(bbox);
        //     //
        //     // await ky.post("/annotation/dynamic/updateBBox", {
        //     //     json: {
        //     //         _token: this._token,
        //     //         idBoundingBox: bbox.idBoundingBox,
        //     //         bbox
        //     //     }
        //     // }).json();
        //     //
        //     // this.object.drawBoxInFrame(this.currentFrame, this.isTracking ? "tracking" : "editing");
        //     //
        //     // console.log("after", this.object.blocked);
        // },

        // async onBBoxChange(bbox) {
        //     console.log("on bbox change ", bbox);
        //     this.object.updateBBox(bbox);
        //     await ky.post("/annotation/dynamic/updateBBox", {
        //         json: {
        //             _token: this._token,
        //             idBoundingBox: bbox.idBoundingBox,
        //             bbox
        //         }
        //     }).json();
        // },

        toggleTracking() {
            console.log("toogle tracking", this.isTracking ? "tracking" : "stopped");
            if (this.isTracking) {
                document.dispatchEvent(new CustomEvent("tracking-stop"));
            } else {
                document.dispatchEvent(new CustomEvent("tracking-start"));
            }
            this.isTracking = !this.isTracking;
        },

        // async onBboxDrawn(e) {
        //     this.bbox = e.detail.bbox;
        //     console.log("bboxDrawn", this.object);
        //     let bbox = new BoundingBox(this.currentFrame, this.bbox.x, this.bbox.y, this.bbox.width, this.bbox.height, true, false);
        //     this.object.addBBox(bbox);
        //     this.interactify(this.object, this.onBBoxChange);
        //     drawBoxObject.disableDrawing();
        //     bbox.idBoundingBox = await ky.post("/annotation/dynamic/createBBox", {
        //         json: {
        //             _token: this._token,
        //             idDynamicObject: this.idDynamicObject,
        //             frameNumber: this.currentFrame,
        //             bbox//     bbox: bbox
        //         }
        //     }).json();
        //     console.log("bbox created: ", bbox.idBoundingBox);
        //     this.tracker.getFrameImage(this.currentFrame);
        //     this.object.drawBoxInFrame(this.currentFrame, "editing");
        //     this.canCreateBBox = false;
        //     messenger.notify("success", "New bbox created.");
        // },


        // annotateObject(object) {
        //     this.object = new DynamicObject(object);
        //     this.object.dom = this.newBboxElement();
        //     this.interactify(this.object, this.onBBoxChange);
        //     let bboxes = object.bboxes;
        //     for (let j = 0; j < bboxes.length; j++) {
        //         let bbox = object.bboxes[j];
        //         let newBBox = new BoundingBox(
        //             parseInt(bbox.frameNumber),
        //             parseInt(bbox.x),
        //             parseInt(bbox.y),
        //             parseInt(bbox.width),
        //             parseInt(bbox.height),
        //             true,
        //             (parseInt(bbox.blocked) === 1),
        //             parseInt(bbox.idBoundingBox)
        //         );
        //         this.object.addBBox(newBBox);
        //     }
        //     this.canCreateBBox = !this.object.hasBBox();
        // },

        // createBBox() {
        //     this.clearFrameObject();
        //     drawBoxObject.enableDrawing();
        //     console.log("Drawing mode activated!");
        // },
        //
        // async toggleTracking() {
        //     console.log("toogle tracking", this.isTracking ? "tracking" : "stopped");
        //     if (this.isTracking) {
        //         this.stopTracking();
        //     } else {
        //         if (this.object.getBoundingBoxAt(this.currentFrame)) {
        //             await this.startTracking();
        //         } else {
        //             messenger.notify("error", "No bbox found at this frame.");
        //         }
        //     }
        // },
        //
        // async startTracking() {
        //     document.dispatchEvent(new CustomEvent("tracking-start"));
        //     this.isTracking = true;
        //     await this.tracking();
        // },
        //
        // async tracking() {
        //     await new Promise(r => setTimeout(r, 800));
        //     const nextFrame = this.currentFrame + 1;
        //     // console.log("tracking....", nextFrame,this.object.startFrame, this.object.endFrame);
        //     if ((this.isTracking) && (nextFrame >= this.object.startFrame) && (nextFrame <= this.object.endFrame)) {
        //         // console.log("goto Frame ", nextFrame);
        //         this.gotoFrame(nextFrame);
        //     } else {
        //         this.stopTracking();
        //     }
        // },
        //
        // stopTracking() {
        //     //console.log("stop tracking");
        //     this.isTracking = false;
        //     document.dispatchEvent(new CustomEvent("tracking-stop"));
        //     this.object.drawBoxInFrame(this.currentFrame, "editing");
        // },
        //
        //
        // newBboxElement: () => {
        //     let dom = document.createElement("div");
        //     dom.className = "bbox";
        //     dom.dataset.blocked = false;
        //     this.boxesContainer.appendChild(dom);
        //     return dom;
        // },
        //
        // interactify: function (object, onChange) {
        //     let dom = object.dom;
        //     let bbox = $(dom);
        //     let canvasHeight = $("#canvas").height();
        //     let canvasWidth = $("#canvas").width();
        //     let drag = document.createElement("div");
        //     drag.className = "handle center-drag";
        //     bbox.append(drag);
        //     let objectId = document.createElement("div");
        //     objectId.className = "objectId";
        //     bbox.append(objectId);
        //     objectId.innerHTML = object.idObject;
        //
        //     //bbox.on('click', () => this.bbox = bbox);
        //
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
        //             if (d.left + $(d.target).outerWidth() > canvasWidth) {
        //                 d.width = canvasWidth - d.left;
        //             }
        //             if (d.top + $(d.target).outerHeight() > canvasHeight) {
        //                 d.height = canvasHeight - d.top;
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
        //             if (d.left + $(d.target).outerWidth() > canvasWidth) {
        //                 d.width = canvasWidth - d.left;
        //             }
        //             if (d.top + $(d.target).outerHeight() > canvasHeight) {
        //                 d.height = canvasHeight - d.top;
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
        //             if (d.left + $(d.target).outerWidth() > $("#canvas").width()) {
        //                 d.left = $("#canvas").width() - $(d.target).outerWidth();
        //             }
        //             if (d.top + $(d.target).outerHeight() > $("#canvas").height()) {
        //                 d.top = $("#canvas").height() - $(d.target).outerHeight();
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
        // },

        // clearFrameObject: function() {
        //     $(".bbox").css("display", "none");
        // },
        //
        gotoFrame(frameNumber) {
            document.dispatchEvent(new CustomEvent("video-seek-frame", {
                detail: {
                    frameNumber
                }
            }));
        },

        // async drawFrameBBox() {
        //     if (this.object) {
        //         this.clearFrameObject();
        //         if (this.isTracking) {
        //             // se está tracking, a box:
        //             // - ou já existe (foi criada antes)
        //             // - ou precisa ser criada
        //             let bbox = this.object.getBoundingBoxAt(this.currentFrame);
        //             if (bbox === null) {
        //                 await this.tracker.setBBoxForObject(this.object, this.currentFrame);
        //                 let bbox = this.object.getBoundingBoxAt(this.currentFrame);
        //                 bbox.blocked = this.object.blocked;
        //                 // console.log("creating bbox at frame", this.currentFrame);
        //                 bbox.idBoundingBox = await ky.post("/annotation/dynamic/createBBox", {
        //                     json: {
        //                         _token: this._token,
        //                         idDynamicObject: this.idDynamicObject,
        //                         frameNumber: this.currentFrame,
        //                         bbox
        //                     }
        //                 }).json();
        //             }
        //         }
        //         // let x = this.object.getBoundingBoxAt(this.currentFrame);
        //         // console.log("drawFrameBBox", this.currentFrame,x ? 'ok' : 'nine');
        //         this.object.drawBoxInFrame(this.currentFrame, this.isTracking ? "tracking" : "editing");
        //     }
        // },

        // async toggleBBoxBlocked() {

            // console.log("blocked", this.object.blocked);
            // this.object.blocked = !this.object.blocked;
            // let bbox = this.object.getBoundingBoxAt(this.currentFrame);
            // console.log(bbox);
            // bbox.blocked = this.object.blocked;
            // this.object.updateBBox(bbox);
            //
            // await ky.post("/annotation/dynamic/updateBBox", {
            //     json: {
            //         _token: this._token,
            //         idBoundingBox: bbox.idBoundingBox,
            //         bbox
            //     }
            // }).json();
            //
            // this.object.drawBoxInFrame(this.currentFrame, this.isTracking ? "tracking" : "editing");
            //
            // console.log("after", this.object.blocked);
        // }

    };
}
