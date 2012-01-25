var reader;
var windows = {};
var controls = {};
var fileQueue = [];
var transmitter = new XMLHttpRequest();

var REGEX_EXTENSIONS = /\.(rar|zip|7z)$/mi;

// ==============
// MAIN EXECUTION
// ==============

function _init() {
    //_initDragSupport();
    controls.filepicker = $('filepicker');
    controls.buttonUpload = $('button_upload');
    controls.buttonAcceptRules = $('button_acceptrules');

    _initDragSupport();
    _reset();
}

function _initDragSupport() {
    reader = new FileReader();
    reader.onload = eventFileLoad;

    // Check browser support
    if (!window.File || !window.FileList || !window.FileReader) {
        delClass(windows.panic, 'hidden');
        return 0;
    }

    window.addEventListener('dragover', eventDrag, false);
    window.addEventListener('drop', eventGetFile, false);
}

function _reset() {
    fileQueue = [];

    controls.buttonUpload.onclick = eventUploadButton;
    controls.filepicker.onchange = eventGetFile;
}

// ===================
// BASIC UPLOAD EVENTS
// ===================

function eventUploadButton(e) {
    if ( isCookie('seenterms') )
        doFilePick();
    else
        doTerms();
}

function doTerms() {
    addClass($('form_upload'), 'rules');
    delClass($('rules'), 'hidden');

    if ( !cookiesEnabled() )
        delClass($('rules_cookieerror'), 'hidden');

    controls.buttonAcceptRules.onclick = function() {
        cookieWrite('seenterms', 'true');

        delClass($('form_upload'), 'rules');
        addClass($('rules'), 'hidden');

        doFilePick();
    }
}

function doFilePick() {
    controls.filepicker.click();
}

function doUpload() {
    progress('Uploading file...');
    $('form_upload').submit();
}

function eventGetFile(e) {
    e.stopPropagation();
    e.preventDefault();

    if (e.type == 'drop')
        fileQueue = e.dataTransfer.files;
    else if (e.type == 'change')
        fileQueue = e.target.files;

    // No acceptable files?
    if ( fileQueue.empty() ) return error('No acceptable files picked!');

    if ( !fileQueue[0].name.match(REGEX_EXTENSIONS) ) return error('File must be a .ZIP, .RAR or .7Z archive! Click to re-choose');

    if ( fileQueue[0].size > 33554432 ) return error('File must be less than 32MB! Click to re-choose');

    controls.buttonUpload.onclick = null;
    addClass(controls.buttonUpload, 'progress');
    delClass(controls.buttonUpload, 'button');

    doUpload();
}

function progress(msg) {
    controls.buttonUpload.innerHTML = msg;
    delClass(controls.buttonUpload, 'error');
}

function error(msg) {
    controls.buttonUpload.innerHTML = msg;
    addClass(controls.buttonUpload, 'error');

    return 0;
}
// ====================
// TODO: DRAG AND DROP EVENTS
// ====================

function eventDrag(e) {
    e.stopPropagation();
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';

    delClass(windows.drophelper, 'hidden');
    window.onmousemove = function(e) {
        if (e.button == 0) {
            addClass(windows.drophelper, 'hidden');
            window.onmousemove = null;
        }
    };
}



function eventFileLoad(e) {
    console.log(e);
    currentLog.raw = e.target.result;

    // Verify log and get time
    if ( currentLog.time = currentLog.raw.match(/^(.*) ModLoader init$/mi) )
        currentLog.time = currentLog.time[1];
    else return error(currentLog.file.name + ' is not a ModLoader log!');

    // Show metadata
    metadata(
        '<b><p>' + currentLog.file.name + '<p></b>' +
        '<p>' + currentLog.file.size + ' bytes</p>' +
        '<p>Logged at ' + currentLog.time + '</p>'
    );

    // Find loaded mods
    currentLog.mods = currentLog.raw.match(/^FINE: Mod Initialized: (.*)$/gmi);
    controls.modlist.innerHTML = null;

    if (currentLog.mods == null) {
        delClass($('step2_skipped'), 'hidden');
        return false;
    }

    delClass($('step2'), 'hidden');

    currentLog.mods.walk( function(mod, key) {
        var modinfo = mod.match(/^FINE: Mod Initialized: "(.*)" from (.*)$/mi);
        controls.modlist.innerHTML += "<div><b>"+modinfo[1]+"</b> from "+modinfo[2]+"</div>" + N;
    });

}