var angularGlobals = {};
var alreadyLoaded = false;
function triggerAngular(locationToGo) {
    $j.ajax({
        url      : '../../rest/initialize',
        type     : 'GET',
        dataType : 'json',
        success: function(answer) {
            angularGlobals = answer;

            if ($j('#inner_content').length > 0) {
                $j('#inner_content').html('<i class="fa fa-spinner fa-spin fa-5x" style="margin-left: 50%;margin-top: 16%;font-size: 8em"></i>');
            } else {
                $j('#loadingContent').html('<i class="fa fa-spinner fa-spin fa-5x" style="margin-left: 50%;margin-top: 16%;font-size: 8em"></i>');
            }

            if (!alreadyLoaded) {
                var head = document.getElementsByTagName('head')[0];

                answer['scriptsToinject'].forEach(function(element, i) {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = "../../dist/" + element;

                    if ((i + 1) === answer['scriptsToinject'].length) {
                        script.onreadystatechange = changeLocationToAngular(locationToGo);
                        script.onload = changeLocationToAngular(locationToGo);
                    }

                    // Fire the loading
                    if (i === 2) {
                        setTimeout(function () {
                            head.appendChild(script);
                        }, 400);
                    } else {
                        head.appendChild(script);
                    }
                });

                var meta = document.createElement('meta');
                meta.name = 'viewport';
                meta.content = "width=device-width, initial-scale=1.0";
                head.appendChild(meta);

                alreadyLoaded = true;
            } else {
                location.href = locationToGo;
            }
        }
    });
}

function changeLocationToAngular(locationToGo) {
    location.href = locationToGo;
}

function lockDocument(resId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=docLocker',
        type : 'POST',
        data: {
            AJAX_CALL  : true,
            lock       : true,
            res_id     : resId
        },
        success: function(result){
        }
    });
}

function unlockDocument(resId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=docLocker',
        type : 'POST',
        data: {
            AJAX_CALL  : true,
            unlock     : true,
            res_id     : resId
        },
        success: function(result) {
        }
    });
}

function islockForSignatureBook(resId, basketId, groupId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=docLocker',
        type : 'POST',
        data: {
            AJAX_CALL  : true,
            isLock     : true,
            res_id     : resId
        },
        success: function(result) {
            var response = JSON.parse(result);

            if (response.lock) {
                alert("Courrier verrouillé par " + response.lockBy);
            } else {
                triggerAngular("#/groups/" + groupId + "/baskets/" + basketId + "/signatureBook/" + resId);
            }
        }
    });
}

var disablePrototypeJS = function (method, pluginsToDisable) {
    var handler = function (event) {
        event.target[method] = undefined;
        setTimeout(function () {
            delete event.target[method];
        }, 0);
    };
    pluginsToDisable.each(function (plugin) {
        $j(window).on(method + '.bs.' + plugin, handler);
    });
};

if (Prototype.BrowserFeatures.ElementExtensions) {
    //FIX PROTOTYPE CONFLICT
    var pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover','tab'];
    disablePrototypeJS('show', pluginsToDisable);
    disablePrototypeJS('hide', pluginsToDisable);
}

function setAttachmentInSignatureBook(id, isVersion) {
    $j.ajax({
        url      : '../../rest/attachments/' + id + '/inSignatureBook',
        type     : 'PUT',
        dataType : 'json',
        data: {
            isVersion   : isVersion
        },
        success: function(answer) {
            if (typeof window.parent['angularSignatureBookComponent'] !== "undefined") {
                window.parent.angularSignatureBookComponent.componentAfterAttach("left");
            }
        }, error: function(err) {
            alert("Une erreur s'est produite");
        }
    });
}

function setSessionForSignatureBook(resId) {
    $j.ajax({
        url: 'index.php?display=true&dir=actions&page=setSession',
        type : 'POST',
        data: {
            resId     : resId
        },
        success: function(result) {
        }
    });
}

function displayThumbnail(resId)
{
    $j('#thumb_' + resId).html('<img src="../../rest/res/' + resId + '/thumbnail">');
}

var koKeys = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];
var koNb = 0;
$j(document).keydown(function (e) {
    if (e.keyCode === koKeys[koNb++]) {
        if (koNb === koKeys.length) {
            var konami = $j("#konami");
            konami.css('top', '200px');
            konami.show();
            var pos = 200;
            var id = setInterval(frame, 10);
            function frame() {
                if (pos > 1200) {
                    clearInterval(id);
                    konami.hide();
                    konami.css('left', '200px');
                } else {
                    pos += 10;
                    konami.css('left', pos + 'px');
                }
            }
            koNb = 0;
        }
    } else {
        koNb = 0;
    }
});
