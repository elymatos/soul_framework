let vatic = {
    bgcolors: [
        "#ffff00",
        "#f21f26",
        "#91c879",
        "#5780d4",
        "#cdeb2d",
        "#4a3c44",
        "#69e2da",
        "#012aaf",
        "#f88006",
        "#53e052",
        "#199601",
        "#ff31d5",
        "#bf5e70",
        "#84059a",
        "#999867",
        "#f8b90d"],
    fgcolors: [
        "#000",
        "#FFF",
        "#000",
        "#000",
        "#000",
        "#FFF",
        "#000",
        "#FFF",
        "#000",
        "#000",
        "#000",
        "#000",
        "#000",
        "#FFF",
        "#000",
        "#000"],

   blobToImage: function (blob) {
        return new Promise((result, _) => {
            let img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = function () {
                result(img);
                URL.revokeObjectURL(this.src);
            };
            img.src = URL.createObjectURL(blob);
        });
    },
    getFramesForObjects: async function (framesManager, config) {
        //framesManager.setConfig(config);
        return {
            totalFrames: () => {
                return framesToLoad.length;
            },
            getFrame: (frameNumber) => {
                return framesManager.getFrame(frameNumber);
            }
        };
    },


    getColor(i) {
        let c = (i % 15);
        return {
            bg: this.bgcolors[c],
            fg: this.fgcolors[c],
        };
    }
};
