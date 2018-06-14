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

function select_template(path_to_script, attachment_type)
{
    
    if ($j('#'+attachment_type.id).val() != '') {
        $j('#formAttachment .transmissionDiv #newAttachButton').prop("disabled",false);
        $j('#formAttachment .transmissionDiv #newAttachButton').removeClass("readonly");
        
    } else {
        $j('#formAttachment .transmissionDiv #newAttachButton').prop("disabled",true);
        $j('#formAttachment .transmissionDiv #newAttachButton').addClass("readonly");
    }
    if ($j('#'+attachment_type.id+' option:selected').attr("width_delay") != '' && $j('#'+attachment_type.id+' option:selected').attr("width_delay") != undefined) {
        var delay = $j('#'+attachment_type.id+' option:selected').attr("width_delay");
        var delay_date = defineBackDate(delay);
        $j('#'+attachment_type.id).parent().parent().find('[name=back_date\\[\\]]').val(delay_date);

    } else {
        $j('#'+attachment_type.id).parent().parent().find('[name=back_date\\[\\]]').val("");
    }

    if ($j('#'+attachment_type.id+' option:selected').val() == "transmission") {
        $j('#'+attachment_type.id).parent().parent().find('[name=effectiveDateStatus\\[\\]]').css('display','inline-block');
        $j('#'+attachment_type.id).parent().parent().find('[name=effectiveDateStatus\\[\\]] option').remove();
        $j('#'+attachment_type.id).parent().parent().find('[name=effectiveDateStatus\\[\\]]').append('<option value="EXP_RTURN">Attente retour</option>');
        $j('#'+attachment_type.id).parent().parent().find('[name=effectiveDateStatus\\[\\]]').append('<option value="NO_RTURN">Pas de retour</option>');
        $j('#'+attachment_type.id).parent().parent().find('[name=back_date\\[\\]]').css('width','75px');
    } else {
        $j('#'+attachment_type.id).parent().parent().find('[name=effectiveDateStatus\\[\\]]').css('display','none');
        $j('#'+attachment_type.id).parent().parent().find('[name=effectiveDateStatus\\[\\]] option').remove();
        $j('#'+attachment_type.id).parent().parent().find('[name=effectiveDateStatus\\[\\]]').append('<option value="A_TRA">A traiter</option>');
        $j('#'+attachment_type.id).parent().parent().find('[name=back_date\\[\\]]').css('width','inherite');
    }

    new Ajax.Request(path_to_script,
    {
        method:'post',
        parameters: {attachment_type: $j('#'+attachment_type.id).val()},
        onSuccess: function(answer){
            $j('#'+attachment_type.id).parent().parent().find('[name=templateOffice\\[\\]]').html(answer.responseText);
            $j('#'+attachment_type.id).parent().parent().find('[name=templateOffice\\[\\]]').change();    
            $j('#'+attachment_type.id).parent().parent().find('[name=contact_attach\\[\\]]').change();
        }
    });
}
