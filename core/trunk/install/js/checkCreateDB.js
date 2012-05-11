function checkCreateDB(
    databasename,
    action
)
{
    $(document).ready(function() {
        var oneIsEmpty = false;
        if (databasename.length < 1) {
            var oneIsEmpty = true;
        }
        if (action.length < 1) {
            var oneIsEmpty = true;
        }

        if (oneIsEmpty) {
            $('#ajaxReturn_createDB_ko').html('Choisissez un nom pour la base de donnée');
            return;
        }
        $('.wait').css('display','block');
        $('#ajaxReturn_createDB_ko').html('');

        ajaxDB(
            'database',
              'databasename|'+databasename
              +'|action|'+action,
            'ajaxReturn_createDB',
            'false'
        );
    });
}
