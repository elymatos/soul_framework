let drawBoxObject = {
    canvas: null,
    ctx: null,
    video: null,
    color: "#000",
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

    config(config, vaticColor) {
        this.canvas = document.getElementById("canvas");
        if (!this.canvas) {
            console.error("Canvas element with ID 'canvas' not found.");
            return;
        }
        this.ctx = this.canvas.getContext("2d");
        this.color = vaticColor;

        console.log("drawBoxObject config applied.");

        this.video = document.getElementById(config.idVideoDOMElement);
        if (this.video) {
            const rect = this.video.getBoundingClientRect();
            this.offsetX = rect.x;
            this.offsetY = rect.y;
        } else {
            console.warn(`Video element with ID '${config.idVideoDOMElement}' not found. Offset will be based on canvas position.`);
            const canvasRect = this.canvas.getBoundingClientRect();
            this.offsetX = canvasRect.left;
            this.offsetY = canvasRect.top;
        }

        this.canvas.width = config.videoDimensions.width;
        this.canvas.height = config.videoDimensions.height;
        this.canvas.style.position = "absolute";
        this.canvas.style.top = "0px";
        this.canvas.style.left = "0px";
        this.canvas.style.backgroundColor = "transparent";
        this.canvas.style.zIndex = 1;
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
            document.dispatchEvent(new CustomEvent("bbox-drawn", {
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
        this.ctx.strokeStyle = this.color.bg;
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

        this.ctx.strokeStyle = this.color.bg;
        this.ctx.strokeRect(this.startX, this.startY, width, height);

        this.box = {
            x: this.startX,
            y: this.startY,
            width: width,
            height: height
        };
    }
};

