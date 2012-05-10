function checkDataDB(
    value
)
{
    $(document).ready(function() {
        if (value != 'default') {
            if (value == 'data') {
                $('#returnCheckDataClassic').css("display","block");
                $('#returnCheckDataMlb').css("display","none");
            } else if (value == 'data_mlb') {
                $('#returnCheckDataClassic').css("display","none");
                $('#returnCheckDataMlb').css("display","block");
            }
        } else {
            $('#returnCheckDataClassic').css("display","none");
            $('#returnCheckDataMlb').css("display","none");
        }
    });
}
