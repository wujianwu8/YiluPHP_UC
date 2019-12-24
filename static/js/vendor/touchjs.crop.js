/*
* author: www.somethingwhat.com
*/
var crop = window.crop || {};
crop.touchjs = {
    left: 0,
    top: 0,
    scaleVal: 1,    //缩放
    rotateVal: 0,   //旋转
    curStatus: 0,   //记录当前手势的状态, 0:拖动, 1:缩放, 2:旋转
    //初始化
    init: function ($touchObj, callback) {
        touch.on($touchObj, 'touchstart', function (ev) {
            crop.touchjs.curStatus = 0;
            ev.preventDefault();//阻止默认事件
        });
        if (!window.localStorage.crop_touchjs_data)
            callback(0, 0, 1, 0);
        else {
            var jsonObj = JSON.parse(window.localStorage.crop_touchjs_data);
            crop.touchjs.left = parseFloat(jsonObj.left), crop.touchjs.top = parseFloat(jsonObj.top), crop.touchjs.scaleVal = parseFloat(jsonObj.scale), crop.touchjs.rotateVal = parseFloat(jsonObj.rotate);
            callback(crop.touchjs.left, crop.touchjs.top, crop.touchjs.scaleVal, crop.touchjs.rotateVal);
        }
    },
    //拖动
    drag: function ($touchObj, callback, $targetObj) {
        touch.on($touchObj, 'drag', function (ev) {
            $targetObj.css("left", crop.touchjs.left + ev.x).css("top", crop.touchjs.top + ev.y);
        });
        touch.on($touchObj, 'dragend', function (ev) {
            crop.touchjs.left = crop.touchjs.left + ev.x;
            crop.touchjs.top = crop.touchjs.top + ev.y;
            callback(crop.touchjs.left, crop.touchjs.top);
        });
    },
    //缩放
    scale: function ($touchObj, callback, $targetObj) {
        var initialScale = crop.touchjs.scaleVal || 1;
        var currentScale;
        touch.on($touchObj, 'pinch', function (ev) {
            if (crop.touchjs.curStatus == 2) {
                return;
            }
            crop.touchjs.curStatus = 1;
            currentScale = ev.scale - 1;
            currentScale = initialScale + currentScale;
            crop.touchjs.scaleVal = currentScale;
            var transformStyle = 'scale(' + crop.touchjs.scaleVal + ') rotate(' + crop.touchjs.rotateVal + 'deg)';
            $targetObj.css("transform", transformStyle).css("-webkit-transform", transformStyle);
            callback(crop.touchjs.scaleVal);
        });

        touch.on($touchObj, 'pinchend', function (ev) {
            if (crop.touchjs.curStatus == 2) {
                return;
            }
            initialScale = currentScale;
            crop.touchjs.scaleVal = currentScale;
            callback(crop.touchjs.scaleVal);
        });
    },
    //旋转
    rotate: function ($touchObj, callback, $targetObj) {
        var angle = crop.touchjs.rotateVal || 0;
        touch.on($touchObj, 'rotate', function (ev) {
            if (crop.touchjs.curStatus == 1) {
                return;
            }
            crop.touchjs.curStatus = 2;
            var totalAngle = angle + ev.rotation;
            if (ev.fingerStatus === 'end') {
                angle = angle + ev.rotation;
            }
            crop.touchjs.rotateVal = totalAngle;
            var transformStyle = 'scale(' + crop.touchjs.scaleVal + ') rotate(' + crop.touchjs.rotateVal + 'deg)';
            $targetObj.css("transform", transformStyle).css("-webkit-transform", transformStyle);
            $targetObj.attr('data-rotate', crop.touchjs.rotateVal);
            callback(crop.touchjs.rotateVal);
        });
    },
    //重置
    reset: function ($targetObj, callback) {
        crop.touchjs.left = 0;
        crop.touchjs.top = 0;
        $targetObj.css("left", 0).css("top", 0);
        var transformStyle = 'scale(1) rotate(0deg)';
        $targetObj.css("transform", transformStyle).css("-webkit-transform", transformStyle);
        $targetObj.attr('data-rotate', 0);
        callback(0);
    }
};