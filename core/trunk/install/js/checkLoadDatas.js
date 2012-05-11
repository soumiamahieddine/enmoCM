function checkLoadDatas(
    dataFilename,
    action
)
{
    $(document).ready(function() {
        var oneIsEmpty = false;
        if (dataFilename.length < 1) {
            var oneIsEmpty = true;
        }
        if (action.length < 1) {
            var oneIsEmpty = true;
        }

        if (oneIsEmpty) {
            $('#ajaxReturn_loadDatas_ko').html('Sélécionner le fichier de datas à importer');
            return;
        }
        $('.wait').css('display','block');
        $('#ajaxReturn_loadDatas_ok').html('');

        ajaxDB(
            'database',
              'dataFilename|'+dataFilename
              +'|action|'+action,
            'ajaxReturn_loadDatas',
            'false'
        );
    });
}
