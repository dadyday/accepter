
(function(){

var Recorder = class {

    constructor() {
        this.recording = false;
        this.mode = null;
        this.data = [];
        var _this = this;
        $('#recordState').on('click', function(ev) { _this.toggle(); });
        $('#recordFuncs button').on('click', function(ev) { _this.interaction($(ev.target)); });
    }

    toggle() {
        if (!this.recording) this.start(); else this.stop();
        console.log(this);
    }

    start() {
        $('#recordResult').remove();
        $('#recordState').addClass('record');
        this.recording = true;
    }

    stop() {
        var json = JSON.stringify(this.data);
        $('#recordBar').append('<span id="recordResult">'+json+'</span>');
        $('#recordState').removeClass('record');
        this.recording = false;
    }

    interaction(el) {
        el.toggleClass('switched');
        this.mode = el.hasClass('switched') ? el.attr('data-mode') : null;
    }

    addEvent(ev, el) {
        var item = {
            mode: this.mode,
            type: ev,
            target: {
                tag: el.tagName,
                id: el.id,
                name: $(el).attr('name'),
                text: el.textContent,
                classList: $(el).attr('class')
            }
        };
        this.data.push(item);
    }

    listener(ev) {
        if (!this.recording) return;
        var bar = $(ev.target).closest('#recordBar');
        if (bar[0]) return;
        this.addEvent(ev.type, ev.target);
    }
};

if (typeof($) == 'undefined') alert('jquery not loaded!');
else {
    window.Recorder = new Recorder();
    console.log(window.Recorder);
    document.body.addEventListener("click", window.Recorder.listener.bind(window.Recorder));
};

})();
