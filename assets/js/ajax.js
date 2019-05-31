"use strict";

let ajax = {};
ajax.x = function () {
    if (typeof XMLHttpRequest !== 'undefined') {
        return new XMLHttpRequest();
    }
    let versions = [
        "MSXML2.XmlHttp.6.0",
        "MSXML2.XmlHttp.5.0",
        "MSXML2.XmlHttp.4.0",
        "MSXML2.XmlHttp.3.0",
        "MSXML2.XmlHttp.2.0",
        "Microsoft.XmlHttp"
    ];

    let xhr;
    for (let i = 0; i < versions.length; i++) {
        try {
            xhr = new ActiveXObject(versions[i]);
            break;
        } 
        catch (e) {}
    }
    return xhr;
};

ajax.send = function (options) {
    let type = options.hasOwnProperty("type") ? options.type.toUpperCase() : "GET";
    let contentType = options.hasOwnProperty("contentType") ? options.contentType : "application/x-www-form-urlencoded;charset=utf-8;";
    let url = options.hasOwnProperty("url") ? options.url : null;
    let data = options.hasOwnProperty("data") ? options.data : null;
    let dataType = options.hasOwnProperty("dataType") ? options.dataType : "text";
    let async = options.hasOwnProperty("async") ? Boolean(options.async) : true;
    let cache = options.hasOwnProperty("cache") ? Boolean(options.cache) : true;
    let onStart = options.hasOwnProperty("onStart") ? options.onStart : null;
    let onFailure = options.hasOwnProperty("onFailure") ? options.onFailure : null;
    let onSuccess = options.hasOwnProperty("onSuccess") ? options.onSuccess : null;
    let onProgress = options.hasOwnProperty("onProgress") ? options.onProgress : null;
    let onEnd = options.hasOwnProperty("onEnd") ? options.onEnd : null;
    let onAbort = options.hasOwnProperty("onAbort") ? options.onAbort : null;
    let onTimeOut = options.hasOwnProperty("onTimeOut") ? options.onTimeOut : null;

    let x = ajax.x();

    x.onloadstart = onStart && {}.toString.call(onStart) === '[object Function]' ? onStart : null;
    x.onprogress = onProgress && {}.toString.call(onProgress) === '[object Function]' ? onProgress : null;
    x.onloadend = onEnd && {}.toString.call(onEnd) === '[object Function]' ? onEnd : null;
    x.onabort = onAbort && {}.toString.call(onAbort) === '[object Function]' ? onAbort : null;
    x.ontimeout = onTimeOut && {}.toString.call(onTimeOut) === '[object Function]' ? onTimeOut : null;
    x.onload = function () {
        if (x.readyState == 4) {
            if(onSuccess && {}.toString.call(onSuccess) === '[object Function]')
            {
                let responseData = null;
                try{
                    responseData = (dataType === "json") ? JSON.parse(x.responseText) : x.responseText;
                }
                catch
                {
                    onFailure(x.responseText);
                    return;
                }
                onSuccess(responseData);
            }
        }
        else if(onFailure && {}.toString.call(onFailure) === '[object Function]')
        {
            onFailure(x.responseText);
        }
    };
    x.onerror = function(){
        if(onFailure && {}.toString.call(onFailure) === '[object Function]')
        {
            onFailure(x.responseText);
        }
    }

    if(type === "GET") {
        let query = [];
        for (let key in data) {
            query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
        }
        !cache ? query.push("_=" + Date.now()) : null;
        url += (query.length ? '?' + query.join('&') : '');
        data = null;
    }
    else if (type === "POST") {
        let TypeContentIsString = typeof contentType === "string" || contentType instanceof String;
        let contentTypeMain = TypeContentIsString ? contentType.split(";")[0] : null;
        let contentTypeIsJson = ["application/json", "application/ld+json"].includes(contentTypeMain);
        if(contentTypeIsJson && typeof data === 'object' && data !== null) {
            data = JSON.stringify(data);
        }
        else if(contentTypeMain === "application/x-www-form-urlencoded") {
            let query = [];
            for (let key in data) {
                query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
            data =  query.join('&');
        }
        let query = [];
        !cache ? query.push("_=" + Date.now()) : null;
        url += (query.length ? '?' + query.join('&') : '');
    }

    x.open(type, url, async);
    if(type === "POST" && contentType !== null && contentType !== "")
    {
        x.setRequestHeader('Content-type', contentType);
    }
    x.send(data)
};