var ZFDebugLoad = window.onload,
    collapsed = collapsed ? collapsed : false;

window.onload = function () {
    if (ZFDebugLoad) {
        ZFDebugLoad();
    }

    if (collapsed) {
        ZFDebugPanel(collapsed);
    }
    document.onmousemove = function (e) {
        var event = e || window.event;
        window.zfdebugMouse = Math.max(40, Math.min(window.innerHeight, -1 * (event.clientY - window.innerHeight - 32)));
    };

    var ZFDebugResizeTimer = null;
    document.getElementById("ZFDebugResize").onmousedown = function () {
        ZFDebugResize();
        ZFDebugResizeTimer = setInterval("ZFDebugResize()", 50);
        return false;
    };
    document.onmouseup = function () {
        clearTimeout(ZFDebugResizeTimer);
    }
};

function ZFDebugResize() {
    window.zfdebugHeight = window.zfdebugMouse;
    document.cookie = "ZFDebugHeight=" + window.zfdebugHeight + ";expires=;path=/";
    document.getElementById("ZFDebug").style.height = window.zfdebugHeight + "px";
    document.getElementById("ZFDebug_offset").style.height = window.zfdebugHeight + "px";

    var panels = document.getElementById("ZFDebug").children;
    for (var i = 0; i < document.getElementById("ZFDebug").childElementCount; i++) {
        if (panels[i].className.indexOf("ZFDebug_panel") == -1)
            continue;

        panels[i].style.height = window.zfdebugHeight - 50 + "px";
    }
}

var ZFDebugCurrent = null;

function ZFDebugPanel(name) {
    if (name == 'collapse') {
        if (document.getElementById("ZFDebug").className == "collapsed") {
            document.getElementById("ZFDebug").className = "";
            document.cookie = "ZFDebugPanelCollapsed=;path=/;expires=" + (new Date(0)).toUTCString();
        } else {
            document.getElementById("ZFDebug").className = "collapsed";
            document.cookie = "ZFDebugPanelCollapsed=;expires=;path=/";
        }
        document.getElementById("ZFDebug").style.height = "32px";
        document.getElementById("ZFDebug_offset").style.height = "32px";
        ZFDebugCurrent = null;
    } else if (ZFDebugCurrent == name) {
        document.getElementById("ZFDebug").style.height = "32px";
        document.getElementById("ZFDebug_offset").style.height = "32px";
        ZFDebugCurrent = null;
        document.cookie = "ZFDebugCollapsed=;expires=;path=/";
    } else {
        if (document.getElementById("ZFDebug").className == "") {
            window.zfdebugHeight = 240;
        }
        document.getElementById("ZFDebug").style.height = window.zfdebugHeight + "px";
        document.getElementById("ZFDebug_offset").style.height = window.zfdebugHeight + "px";
        ZFDebugCurrent = name;

        document.cookie = "ZFDebugCollapsed=" + name + ";expires=;path=/";
    }

    var panels = document.getElementById("ZFDebug").children;
    for (var i = 0; i < document.getElementById("ZFDebug").childElementCount; i++) {
        if (panels[i].className.indexOf("ZFDebug_panel") == -1)
            continue;

        if (ZFDebugCurrent && panels[i].id == name) {
            document.getElementById("ZFDebugInfo_" + name.substring(8)).className += " ZFDebug_active";
            panels[i].style.display = "block";
            panels[i].style.height = (window.zfdebugHeight - 50) + "px";
        } else {
            var element = document.getElementById("ZFDebugInfo_" + panels[i].id.substring(8));
            element.className = element.className.replace("ZFDebug_active", "");
            panels[i].style.display = "none";
        }
    }
}