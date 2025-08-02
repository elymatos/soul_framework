<div
    id="videoComponent"
    x-data="videoComponent()"
    @object-selected.document="onObjectSelected"
    @video-seek-frame.document="onVideoSeekFrame"
    @video-toggle-play.document="onVideoTogglePlay"
    style="position:relative; width:852px;height:480px"
>
    <video-js
        id="videoContainer"
        class="video-js"
        src="{!! config('webtool.mediaURL') . "/" . $video->currentURL !!}"
    >
    </video-js>
    <div id="boxes">

    </div>

</div>
