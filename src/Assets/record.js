
(function(){

    var Recorder = window.Recorder = function() {
    };
    Recorder.data = null;

    Recorder.create = function() {
        $('#recordState').on('click', Recorder.toggle);
    };

    Recorder.isRecording = function() {
        return $('#recordState').hasClass('record');
    };

    Recorder.toggle = function() {
        var on = !Recorder.isRecording();
        if (on) Recorder.start(); else Recorder.stop();
    };

    Recorder.start = function() {
        $('#recordBar > #recordResult').remove();
        $('#recordState').addClass('record');
    };

    Recorder.stop = function() {
        $('#recordState').removeClass('record');
        $('#recordBar').append('<span id="recordResult">'+Recorder.data+'</span>');
    };

    Recorder.listener = function(ev) {
        if (!Recorder.isRecording()) return;
        if (ev.target.id == 'recordState') return;
        var data = {
            type: ev.type,
            target: {
                tag: ev.target.tagName,
                id: ev.target.id,
                name: ev.target.name,
                text: ev.target.innerText,
                classList: ev.target.classList
            }
        };
        var json = JSON.stringify(data);
        Recorder.data = json;
    };

    if (typeof($) == 'undefined') alert('jquery not loaded!');
    else {
        Recorder.create();
        document.body.addEventListener("click", Recorder.listener);
    };
})();
