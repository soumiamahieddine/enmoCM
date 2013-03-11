function showEmailForm(path, width, height) {
    
    if(typeof(width)==='undefined'){
        var width = '800px';
    }
    
    if(typeof(height)==='undefined'){
        var height = '540px';
    }  
    
    new Ajax.Request(path,
    {
        method:'post',
        parameters: { url : path
                    },  
        onSuccess: function(answer) {
            eval("response = "+answer.responseText);
           
            if(response.status == 0){
             
                var modal_content = convertToTextVisibleNewLine(response.content);
                createModal(modal_content, 'form_email', height, width); 
            } else {
                window.top.$('main_error').innerHTML = response.error;
            }
        }
    });
}

function validEmailForm (path, form_id) {
    // var bodyContent = getBodyConten();

    new Ajax.Request(path,
    {
        asynchronous:false,
        method:'post',
        parameters: Form.serialize(form_id),   
        encoding: 'UTF-8',                       
        onSuccess: function(answer){
            eval("response = "+answer.responseText);
            if(response.status == 0){
                eval(response.exec_js);
                window.parent.destroyModal('form_email'); 
            } else {
                alert(response.error);
                eval(response.exec_js);
            }
        }
    });
}

var adress = { 
    adresses : [],
    modifyAdress : function(id, value) {
        var index = this.adresses.indexOf(this.adresses[id]);
        if(index < 0) {
            return;
        }
        this.adresses[index].value = this.adresses[id].value = value;
    },
    addAdress : function(id, value) { 
        if(this.adresses[id]) { 
            this.modifyAdress(id, value);
            return;
        }
        this.adresses[id] = { 
            id : id, 
            value : value,
            toString : function() { 
                return "\"" + this.id + "\" <" + this.value + ">"; 
            },
        };
        this.adresses.push(this.adresses[id]);
    },
    removeAdress : function(id) { 
        if(!this.adresses[id]) return;
        this.adresses.splice(this.adresses.indexOf(this.adresses[id]), 1);
        delete this.adresses[id];
    },
    toString : function() {
        return this.adresses.join(", ");
    },
};

var addEmailAdress = function (idField, idList, theUrlToListScript, paramNameSrv, minCharsSrv) {
     new Ajax.Autocompleter(
         idField,
         idList,
         theUrlToListScript,
         {
             paramName: paramNameSrv,
             minChars: minCharsSrv,
             afterUpdateElement:extractEmailAdress
         });
 };
 
var savedArray = new Array();
function saveField(field) {
    savedArray = field.value.split(", ");
   
    for (var i = 0; i < savedArray.length; i++) {
        
        if (validateEmail(savedArray[i]) === false) {
            alert("NOK: " + savedArray[i]);
        } else {
            alert("OK: " + savedArray[i]);
        }
    }
}

function extractEmailAdress(field, item) {
    var fullAdress = item.innerHTML;
    var email = fullAdress.match(/\(([^)]+)\)/)[1];
    adress.addAdress(email, email);
    field.value = adress.toString();
}

function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}
 
function switchMode(action) {
    var div = document.getElementById(mode+"_mode");
    div.style.display = "block";
    if(action == "show") {
        div.style.display = "none"; // Hide the current div.
        mode = (mode === 'html')? 'raw' : 'html';      // switch the mode
        document.getElementById("is_html").value = (mode === 'html')? 'Y' : 'N'; //Update the hidden field
        document.getElementById(mode+"_mode").style.display = "block"; // Show the other div.
    }
}