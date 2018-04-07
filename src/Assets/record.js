
(function(){

var Recorder = class {

    constructor() {
        this.state = 'init';
        this.mode = null;
        this.data = [];
        var _this = this;
        $('#recordState').on('click', function(ev) { _this.toggle(); });
        $('#recordFuncs button').on('click', function(ev) { _this.interaction($(ev.target)); });
    }

    signal(state) {
        this.state = state;
        // TODO: await Promise
        //var n = 100000;
        //while (n > 0 && this.state == state) n--;
        //if (!n) alert(this.state);
    }

    sendData() {
        this.signal('data');
    }

    toggle() {
        if (this.state != 'recording') this.start(); else this.stop();
    }

    start() {
        $('#recordState').addClass('record');
        this.restart();
    }

    restart() {
        this.state = 'recording';
    }

    reload() {
        this.sendData();
        $('#recordState').addClass('reload');
        this.signal('reload');
    }

    stop() {
        this.sendData();
        $('#recordState').removeClass('record');
        this.state = 'playback';
    }

    interaction(el) {
        $('.switched', $(el).closest('#recordBar')).removeClass('switched');
        el.toggleClass('switched');
        this.mode = el.hasClass('switched') ? el.attr('data-mode') : null;
    }

    addEvent(ev, el) {
        if (!this.mode) return;
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
        if (this.state != 'recording') return;
        var bar = $(ev.target).closest('#recordBar');
        if (bar[0]) return;
        if (ev.type == 'unload') this.reload();
        else this.addEvent(ev.type, ev.target);
    }

};

if (typeof($) == 'undefined') alert('jquery not loaded!');
else {
    window.Recorder = new Recorder();
    console.log(window.Recorder);
    document.body.addEventListener("click", window.Recorder.listener.bind(window.Recorder));
    document.body.addEventListener("unload", window.Recorder.listener.bind(window.Recorder));
};

})();
