function createDocservers(
    docserverRoot
)
{
    $(document).ready(function() {
        var oneIsEmpty = false;
        if (docserverRoot.length < 1) {
            var oneIsEmpty = true;
        }

        if (oneIsEmpty) {
            $('#ajaxReturn_createDocservers_ko').html('Vous devez choisir l\'emplacement racine de vos docservers');
            return;
        }
        $('#ajaxReturn_createDocservers_ko').html('');

        ajaxDB(
            'docservers',
              'docserverRoot|'+docserverRoot,
            'ajaxReturn_createDocservers',
            'false'
        );

    });
}
