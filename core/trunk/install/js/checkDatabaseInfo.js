function checkDatabaseInfo(
    databaseserver,
    databaseserverport,
    databaseuser,
    databasepassword,
    databasetype,
    action
)
{
    $(document).ready(function() {
        var oneIsEmpty = false;
        if (databaseserver.length < 1) {
            var oneIsEmpty = true;
        }
        if (databaseserverport.length < 1) {
            var oneIsEmpty = true;
        }
        if (databaseuser.length < 1) {
            var oneIsEmpty = true;
        }
        if (databasepassword.length < 1) {
            var oneIsEmpty = true;
        }
        if (databasetype.length < 1) {
            var oneIsEmpty = true;
        }
        if (action.length < 1) {
            var oneIsEmpty = true;
        }

        if (oneIsEmpty) {
            $('#ajaxReturn_testConnect_ko').html('au moins un champ mal rempli');
            return;
        }
        $('.wait').css('display','block');
        $('#ajaxReturn_testConnect_ko').html('');

        ajaxDB(
            'database',
              'databaseserver|'+databaseserver
              +'|databaseserverport|'+databaseserverport
              +'|databaseuser|'+databaseuser
              +'|databasepassword|'+databasepassword
              +'|databasetype|'+databasetype
              +'|action|'+action,
            'ajaxReturn_testConnect',
            'false'
        );

    });
}
