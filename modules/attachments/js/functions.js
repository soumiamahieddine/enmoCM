function cleanTitle(str) {
    //permet de supprimer les # dans le titre qui bloque l'ouverture de l'applet java
    var res = str.replace(/#/g, " ");
    return(res);
}

function activePjTab(target) {

    $j('[id^=PjDocument_],#MainDocument').css('background-color', 'rgb(197, 197, 197)');
    $j('[id^=PjDocument_],#MainDocument').css('height', '21px');
    $j('[id^=iframePjDocument_],#iframeMainDocument').hide();

    $j('#'+target.id).css('background-color', 'white');
    $j('#'+target.id).css('height', '23px');
    $j('#iframe'+target.id).show();
}
