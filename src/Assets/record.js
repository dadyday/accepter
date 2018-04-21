
(function(){

var Recorder = class {

    constructor() {
        this.state = 'init';
        this.mode = null;
        this.data = [];
        this.transmit = [];
        this.lastElement = null;
        var _this = this;
        $('#recordState').on('click', ev => this.toggle());
        $('#recordFuncs button').on('click', ev => this.interaction($(ev.target)));
    }

    signal(state) {
        this.state = state;
        // TODO: await Promise
        //var n = 100000;
        //while (n > 0 && this.state == state) n--;
        //if (!n) alert(this.state);
    }

    sendData() {
        this.transmit = [];
        if (!this.data.length) return;
        this.transmit = this.data;
        this.data = [];
        this.lastElement = null;
        this.signal('data');
        //console.log('sendData', this);
    }

    transmitData() {
        return this.transmit;
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

    getCursor() {
        switch (this.mode) {
            case 'see': return 'help'; break;
            case 'wait': return 'progress'; break;
            case 'mouse': return 'crosshair'; break;
            case 'keys': return 'text'; break;
        };
        return null;
    }

    interaction(el) {
        $('.switched', $(el).closest('#recordBar')).removeClass('switched');
        el.toggleClass('switched');
        this.mode = el.hasClass('switched') ? el.attr('data-mode') : null;
    }

    highlite(el, onoff) {
        el = $(el);
        if (onoff && this.state == 'recording') {

            el.data('oldStyle', {
                background: el.css('background'),
                cursor: el.css('cursor'),
            });
            el.css({
                background: '#fee',
                cursor: this.getCursor(),
            });
        }
        else {
            var oldStyle = el.data('oldStyle');
            el.css(oldStyle);
            el.data('oldStyle', {});
        }
    }

    recordEvent(ev) {
        if (!this.mode) return;
        if (ev.target != this.lastElement) this.sendData();
        this.lastElement = ev.target;

        var args = {};
        switch (ev.type) {
            case 'keydown':
            case 'keypress':
            case 'keyup':
                args.key = ev.key;
                args.code = ev.code;
                args.alt = ev.altKey;
                args.ctrl = ev.ctrlKey;
                args.shift = ev.shiftKey;
                args.meta = ev.metaKey;
                args.hold = ev.repeat;
                break;
        }

        this.addEvent(this.mode, ev.type, args, ev.target);
        return true;
    }

    addEvent(mode, type, args, target) {
        var sel = window.getSelection();
        var item = {
            mode: mode,
            type: type,
            args: args,
            target: {
                tag: target.tagName,
                id: target.id,
                name: $(target).attr('name'),
                value: $(target).val(),
                text: target.textContent,
                classes: $(target).attr('class'),
                attributes: target.attributes,
                selection: {
                    type: sel.type.toLowerCase(),
                    text: sel.toString(),
                }
            }
        };
        this.data.push(item);
    }

    listener(ev) {
        if (this.state != 'recording') return;
        var bar = $(ev.target).closest('#recordBar');
        if (bar[0]) return;

        if (ev.target.tagName == 'BODY') return;
        if (ev.type == 'unload') return this.reload();
        if (ev.type == 'mouseover') return this.highlite(ev.target, true);
        if (ev.type == 'mouseout') return this.highlite(ev.target, false);

        return this.recordEvent(ev);
    }

};

if (typeof($) == 'undefined') alert('jquery not loaded!');
else {
    window.Recorder = new Recorder();
    console.log(window.Recorder);
    document.body.addEventListener("mouseover", window.Recorder.listener.bind(window.Recorder));
    document.body.addEventListener("mouseout", window.Recorder.listener.bind(window.Recorder));
    document.body.addEventListener("click", window.Recorder.listener.bind(window.Recorder));
    document.body.addEventListener("keydown", window.Recorder.listener.bind(window.Recorder));
    document.body.addEventListener("unload", window.Recorder.listener.bind(window.Recorder));
};

})();
