function videoComponent() {
    return {
        idVideoJs: "videoContainer",
        idVideo: "videoContainer_html5_api",
        fps: 25, // frames por segundo
        timeInterval: 0.04, // interval between frames - 0.04s = 40ms
        dimensions: {
            width: 852,
            height: 480
        },
        player: null,
        frame: {
            current: 1,
            last: 0
        },
        time: {
            current: 0.0
        },
        isPlaying: false,

        init() {
            console.log("videoComponent init");
            this.player = videojs(this.idVideoJs, {
                height: this.dimensions.height,
                width: this.dimensions.width,
                controls: true,
                autoplay: false,
                preload: "auto",
                playbackRates: [0.2, 0.5, 0.8, 1, 2],
                bigPlayButton: false,
                inactivityTimeout: 0,
                children: {
                    controlBar: {
                        playToggle: false,
                        volumePanel: false,
                        remainingTimeDisplay: false,
                        fullscreenToggle: false,
                        pictureInPictureToggle: false
                    },
                    mediaLoader: true,
                    loadingSpinner: true
                },
                userActions: {
                    doubleClick: false
                }
            });
            let player = this.player;
            let video = this;

            drawBoxObject.config({
                idVideoDOMElement: this.idVideo,
                videoDimensions: this.dimensions
            }, vatic.getColor(0));
            // Initially disable drawing
            drawBoxObject.disableDrawing();

            player.crossOrigin("anonymous");

            player.player_.handleTechClick_ = () => {
                this.togglePlay();
            };

            player.ready(() => {
                player.on("durationchange", () => {
                    let duration = player.duration();
                    let lastFrame = this.frameFromTime(duration);
                    document.dispatchEvent(new CustomEvent("video-update-duration", {
                        detail: {
                            duration,
                            lastFrame
                        }
                    }));
                });

                player.on("timeupdate", () => {
                    this.time.current = player.currentTime();
                    this.frame.current = this.frameFromTime(this.time.current);
                    this.broadcastState();
                });

                player.on("play", () => {
                    this.isPlaying = true;
                    this.broadcastState();
                });

                player.on("pause", () => {
                    this.isPlaying = false;
                    this.broadcastState();
                });
            });
        },

        onVideoSeekFrame(e) {
            this.seekToFrame(e.detail.frameNumber);
        },

        onVideoTogglePlay(e) {
            this.togglePlay();
        },

        broadcastState() {
            // Send current state to controls
            // console.log("broadcastState", this.frame.current, this.time.current, this.isPlaying);
            document.dispatchEvent(new CustomEvent('video-update-state', {
                detail: {
                    frame: this.frame,
                    time: this.time,
                    isPlaying: this.isPlaying
                }
            }));
        },

        seekToFrame(frame) {
            if (frame !== this.frame.current) {
                this.player.currentTime(this.timeFromFrame(frame));
            }
        },

        togglePlay() {
            if (this.isPlaying) {
                this.player.pause();
            } else {
                this.player.play();
            }
        },

        frameFromTime(timeSeconds) {
            //return Math.floor(parseFloat(timeSeconds.toFixed(3)) * this.fps) + 1;
            return Math.floor((timeSeconds * 1000 * this.fps) / 1000) + 1;
        },
        timeFromFrame(frameNumber) {
            return Math.floor(((frameNumber - 1) * this.timeInterval) * 1000) / 1000;
        },
        playByRange(startTime, endTime, offset) {
            let playRange = {
                startFrame: this.frameFromTime(startTime - offset),
                endFrame: this.frameFromTime(endTime + offset)
            };
            this.playRange(playRange);
        },
        playByFrameRange(startFrame, endFrame) {
            let playRange = {
                startFrame: startFrame,
                endFrame: endFrame
            };
            this.playRange(playRange);
        },
        playRange(range) {
            this.playingRange = range;
            this.gotoFrame(range.startFrame);
            this.player.play();
        }

    };
}
