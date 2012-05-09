function ajax(
    url,
    parameters,
    divRetour,
    top
)
{
    var ajaxUrl  = url;

    var parametersTemp = parameters.split('|');

    var strAjaxParameters = '{';
    for (cpt=0; cpt<parametersTemp.length; cpt++) {
        strAjaxParameters += parametersTemp[cpt];
        strAjaxParameters += ":";
        strAjaxParameters += "'";
        cpt++;
        strAjaxParameters += parametersTemp[cpt];
        strAjaxParameters += "'";
        if (cpt < parametersTemp.length) {
            strAjaxParameters += ", ";
        }
    }
    strAjaxParameters += "ajax:'true'";
    strAjaxParameters += ", div:'"+divRetour+"'";
    strAjaxParameters += '}'

    var ajaxParameters = eval('(' + strAjaxParameters + ')');

    /**********/

    if (top == 'true') {
        var retour = window.top.$('#'+divRetour);
    } else {
        var retour = $('#'+divRetour);
    }

    /**********/

    $(document).ready( function() {
        $.getJSON('ajax.php?script='+ajaxUrl, ajaxParameters, function(data){
            if (data.status == 1) {
                retour.html(data.text);
            } else {
                retour.html(data.text);
            }
        });
    });
}
