function actionSeda($path,$type) {
    if ($type == 'zip') {
        window.open($path);
    } else {
    	new Ajax.Request($path,
            {
                method:'post',
                parameters: { url : $path,
                            },
                onSuccess: function(answer) {
                    console.log(answer);

                    eval("response = "+answer.responseText);
                    if(response.status == 0){
                        console.log(response);
                        if ($type != "validateMessage") {
                            $("validSeda").style.display = 'block';
                            $("sendMessage").style.display = 'none';

                            alert(response.content);
                        } else {
                            $("cancel").click();
                            location.reload();
                        }
                        
                    } else {
                        alert(response.error);
                    }
                }
            });
    }
}