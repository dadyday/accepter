
(function(){

    var Recorder = window.Recorder = function() {
    };

    Recorder.create = function() {
        var div = document.createElement("div");
        div.innerHTML = "\
            <span id=recordState onclick='Recorder.stop();'>\
                playback\
            </span>\
            &lt;- click if youre ready\
            <span id=recordData style='visibility: hidden'>-</span>";
        document.body.appendChild(div);
    };

    Recorder.start = function() {
        document.getElementById("recordState").innerText = 'record';
    };

    Recorder.stop = function() {
        document.getElementById("recordData").style.visibility = 'visible'; // wont be found by wd
        document.getElementById("recordState").innerText = 'stop';
    };

    Recorder.listener = function(ev) {
        console.log(ev.target);
        if (document.getElementById("recordState").innerText != 'record') return;
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
        document.getElementById("recordData").innerText = json;
    };


    Recorder.create();
    document.body.addEventListener("click", Recorder.listener);

})();
