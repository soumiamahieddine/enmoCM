function actionSeda($path) {
	new Ajax.Request($path,
        {
            method:'post',
            parameters: { url : $path,
                        },
            onSuccess: function(answer) {
                eval("response = "+answer.responseText);
                if(response.status == 0){
                    var btn = $('<input type="button" value="_VALIDATE"/>');
                    $("validSeda").append(btn);
                } else {
                    alert(response.error);
                    eval(response.exec_js);
                }
                // $('loading_' + target).style.display='none';
            }
        });
}