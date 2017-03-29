function show_templates(show)
{
    var div = $('templates_div');
    if (div != null) {
        if (show == true) {
            div.style.display = 'block';
        } else {
            div.style.display = 'none';
            var list = $('templates');
            list.selectedIndex = 0;
        }
    }
}

function show_special_form(elem_to_view, elem_to_hide1)
{
    var elem_1 = window.document.getElementById(elem_to_view);
    var elem_2 = window.document.getElementById(elem_to_hide1);
    if (elem_1 != null) {
        elem_1.style.display = "block";
    }
    if (elem_2 != null) {
        elem_2.style.display = "none";
    }
}

function show_special_form_3_elements(elem_to_view, elem_to_hide1, elem_to_hide2)
{
    console.log(elem_to_view);
    var elem_0 = window.document.getElementById(elem_to_view);
    var elem_1 = window.document.getElementById(elem_to_hide1);
    var elem_2 = window.document.getElementById(elem_to_hide2);
    if (elem_0 != null) {
        elem_0.style.display = "block";
    }
    if (elem_1 != null) {
        elem_1.style.display = "none";
    }
    if (elem_2 != null) {
        elem_2.style.display = "none";
    }
}

function changeStyle(style_id, path_to_script) 
{
    //window.alert(path_to_script);
    new Ajax.Request(path_to_script,
    {
        method:'post',
        parameters: {template_style: style_id.value},
        onSuccess: function(answer){
            eval("response = "+answer.responseText)
        },
        onFailure: function() {
            $('modal').innerHTML = '<div class="error"><?php echo _SERVER_ERROR;?></div>'+form_txt;
            form.query_name.value = this.name;
        }
    });
}

function setradiobutton(target)
{
    $j("#html,#office,#txt,#span_html,#span_office,#span_txt").css({"display":"inline"});
    $j("#template_attachment_type_tr").hide();

    if(target=="notes") {
        $j("#html,#span_html,#office,#span_office").hide();
        $j("#txt").click();
    }else if(target=="sendmail") {
        $j("#office,#span_office").hide();
        $j("#html").click();
        console.log("office caché !");
    } else if(target=="notifications") {
        $j("#txt,#span_txt,#office,#span_office").hide();
        $j("#html").click();
    } else if (target=="attachments"){
        $j("#txt,#span_txt,#html,#span_html").hide();
        $j("#office").click();
        $j("#template_attachment_type_tr").css({"display":"inline"});
    } else if(target=="doctypes") {
        $j("#txt,#span_txt,#office,#span_office").hide();
        $j("#html").click();
    }

    if (target != "attachments") {
    	$j("#template_attachment_type").selectedIndex="0";
    }
}

function select_template(path_to_script, attachment_type)
{
    new Ajax.Request(path_to_script,
    {
        method:'post',
        parameters: {attachment_type: attachment_type},
        onSuccess: function(answer){
            $('templateOffice').innerHTML = answer.responseText;
            if (typeof ($('templateOffice').onchange) == 'function')
                $('templateOffice').onchange();
        }
    });
}