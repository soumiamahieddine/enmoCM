function loadApplet(url)
{
    //window.alert(url);
    displayModal(url, 'CMApplet', 300, 300);
}

function endOfApplet(theMsg)
{
    //$('maarchcm').innerHTML = 'edition OK';
    if (theMsg != '') {
        $('maarchcm').innerHTML = theMsg;
    } else {
        destroyModal('CMApplet');
    }
}
