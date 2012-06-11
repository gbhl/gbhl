function eventTrigger (e) {
    if (! e)
        e = event;
    return e.target || e.srcElement;
}

function radioClick1 (e) {
    var obj = eventTrigger (e);
    var notify = document.getElementById &&
                    document.getElementById ('id1');
    if (notify)
        notify.value = obj.value;
    return true;
}

function radioClick2 (e) {
    var obj = eventTrigger (e);
    var notify = document.getElementById &&
                    document.getElementById ('id2');
    if (notify)
        notify.value = obj.value;
    return true;
}
