<div x-data="videoComponent()" class="video-player-container">
    <div class="video-wrapper"
         style="position:relative; width:852px;height:480px"
    >
        <video :id="idVideo"
               preload="metadata"
               crossorigin="anonymous"
               x-init="init()"
               @loadstart="log('Load start')"
               @loadedmetadata="onLoadedMetadata()"
               @loadeddata="log('Data loaded')"
{{--               @video-toggle-play.document="onVideoTogglePlay()"--}}
               @video-seek-frame.document="onVideoSeekFrame"
{{--               @video-update-state.document="onVideoUpdateState"--}}
{{--               @video-update-duration.document="onVideoUpdateDuration"--}}
               @tracking-start.document="onTrackingStart"
               @tracking-stop.document="onTrackingStop"
               @canplay="log('Can play')"
               @canplaythrough="log('Can play through')"
               @durationchange="onDurationChange()"
               @timeupdate="onTimeUpdate()"
               @play="onPlay()"
               @pause="onPause()"
               @seeking="onSeeking()"
               @seeked="onSeeked()"
               @progress="updateBuffer()"
               @click="togglePlay()">
            <source src="{!! config('webtool.mediaURL') . "/" . $video->currentURL !!}?t={!! time() !!}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <video id="fallbackVideo" style="display:none"></video>
        <!-- Progress bar -->
        <div class="progress-container" @click="seekToPosition($event)">
            <div class="buffer-bar" :style="'width: ' + bufferProgress + '%'"></div>
            <div class="progress-bar" :style="'width: ' + playProgress + '%'"></div>
        </div>

        <div x-show="isSeekingInProgress"
             class="seeking-indicator spinning"
             x-text="'Seeking to frame ' + seekingToFrame + '...'">
        </div>

        <canvas id="canvas" width=852 height=480 style="position: absolute; top: 0; left: 0; background-color: transparent; z-index: 1;"></canvas>

        <div id="boxesContainer">

        </div>
    </div>
    <div
        class="control-bar flex-container between"
    >
        <div style="width:128px;text-align:left;">
            <div class="ui label bg-gray-300">
                <span x-text="frame.current"></span> [<span
                    x-text="formatTime(time.current)"></span>]
            </div>
        </div>
        <div id="videoNavigation" class="ui small basic icon buttons">
            <button
                class="ui button nav"
                :class="(isPlaying || isTracking) && 'disabled'"
                @click="gotoStart()"
            ><i class="fast backward icon"></i>
            </button>
            <button
                class="ui button nav"
                :class="(isPlaying || isTracking) && 'disabled'"
                @click="gotoPrevious10Second()"
            ><i class="backward icon"></i>
            </button>
            <button
                class="ui button nav"
                :class="isPlaying && 'disabled'"
                @click="gotoPreviousFrame()"
            ><i class="step backward icon"></i>
            </button>
            <button
                class="ui button toggle"
                :class="isTracking && 'disabled'"
                @click="togglePlay()"
            ><i :class="isPlaying ? 'pause icon' : 'play icon'"></i>
            </button>
            <button
                class="ui button nav"
                :class="isPlaying && 'disabled'"
                @click="gotoNextFrame()"
            ><i class="step forward icon"></i>
            </button>
            <button
                class="ui button nav"
                :class="(isPlaying || isTracking) && 'disabled'"
                @click="gotoNext10Second()"
            ><i class="forward icon"></i>
            </button>
            <button
                class="ui button nav"
                :class="(isPlaying || isTracking) && 'disabled'"
                @click="gotoEnd()"
            ><i class="fast forward icon"></i>
            </button>
        </div>
        <div style="width:128px;text-align:right;">
            <div class="ui label bg-gray-300">
                <span x-text="frame.last"></span> [<span
                    x-text="formatTime(duration)"></span>]
            </div>
        </div>
    </div>

</div>

