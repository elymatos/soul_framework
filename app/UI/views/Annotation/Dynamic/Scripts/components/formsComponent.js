function formsComponent(idDocument) {
    return {
        idDocument: 0,
        formsPane: null,
        currentFrame: 0,
        isPlaying: false,
        isTracking: false,
        bboxDrawn: null,

        init() {
            this.idDocument = idDocument;
            this.formsPane = document.getElementById("formsPane");
        },

        onVideoUpdateState(e) {
            this.currentFrame = e.detail.frame.current;
            this.isPlaying = e.detail.isPlaying;
        },

        onBBoxToggleTracking() {
            this.isTracking = !this.isTracking;
            if (this.isTracking) {
                document.dispatchEvent(new CustomEvent("tracking-start"));
            } else {
                document.dispatchEvent(new CustomEvent("tracking-stop"));
            }
        },

        onBBoxDrawn(e) {
            console.log("onBBoxDrawn", e.detail.bbox);
            this.bboxDrawn = e.detail.bbox;
        },

        onCloseObjectPane() {
            window.location.assign(`/annotation/dynamic/${this.idDocument}`);
        },

        copyFrameFor(name) {
            console.log(name);
            const input = document.querySelector(`input[name="${name}"]`);
            input.value = this.currentFrame;
        }

    };
}
