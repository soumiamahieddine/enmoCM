function checkDatabaseInfo(
    databaseserver,
    databaseserverport,
    databaseuser,
    databasepassword,
    databasename,
    databasetype
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
        if (databasename.length < 1) {
            var oneIsEmpty = true;
        }
        if (databasetype.length < 1) {
            var oneIsEmpty = true;
        }

        if (oneIsEmpty) {
            $('#returnCheckDatabaseInfo').html('au moins un champ mal rempli');
            return;
        }
        $('#returnCheckDatabaseInfo').html('');

        ajax(
            'database',
              'databaseserver|'+databaseserver
              +'|databaseserverport|'+databaseserverport
              +'|databaseuser|'+databaseuser
              +'|databasepassword|'+databasepassword
              +'|databasename|'+databasename
              +'|databasetype|'+databasetype,
            'returnCheckDatabaseInfo',
            'false'
        );

    });
}
