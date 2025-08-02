<div
    x-data="navigationComponent()"
    @video-update-state.document="onVideoUpdateState"
    @video-update-duration.document="onVideoUpdateDuration"
    @tracking-start.document="onTrackingStart"
    @tracking-stop.document="onTrackingStop"
    class="control-bar flex-container between"
>
    <div style="width:128px;text-align:left;">
        <div class="ui label bg-gray-300">
            <span x-text="frame.current"></span> [<span
                x-text="time.current"></span>]
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
            @click="toggle()"
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
                x-text="time.duration"></span>]
        </div>
    </div>
</div>

