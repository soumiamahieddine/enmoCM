function cleanTitle(str) {
    //permet de supprimer les # dans le titre qui bloque l'ouverture de l'applet java
    var res = str.replace(/#/g, " ");
    return(res);
}

// Function to go back in case of error while selecting a document
function historyBack(errorMessage){
    if(confirm(errorMessage)){
        history.back();
    }else history.back();
}

// Function to display the necessary informations after selecting a document
function displayInfos(chrono_number, title, contactid, addressid, placeholder, listchrononumber, hiddendata, res_id){
    var chrono_res = window.opener.$('chrono_number');
    var chrono_tr = window.opener.$('chrono_number_tr');
    var attachment_type_tr = window.opener.$('attachment_type_tr'); // NCH01 new modifs

    var list_chrono = window.opener.$('list_chrono_number');
    var list_chrono_tr = window.opener.$('list_chrono_number_tr');
    // Empty field, in case the users fails the first time and want to retry
    chrono_res.value = '';
    chrono_tr.style.display = 'table-row';
    chrono_res.removeAttribute('readonly');
    chrono_res.removeAttribute('class', 'readonly');

    list_chrono_tr.style.display = 'none';
    attachment_type_tr.style.display = 'table-row'; // NCH01 new modifs

    // Enable the chrono number generator (For Orange customer the generate chrono number button is always enabled)
    window.opener.$('chrono_number_generate').style.display = 'table-row';
    if (!chrono_number) {
        // Add placeholder
        window.opener.$('chrono_number').setAttribute('placeholder', placeholder);
    }else {
        // Fill the chrono_number input with the project response identifier and set it read_only
        chrono_res.value = chrono_number;
        chrono_res.setAttribute('readonly', 'readonly');
        chrono_res.setAttribute('class', 'readonly');
    }

    if (contactid && addressid){
        // Fill the hidden input, to get the contact info after the form validation
        window.opener.$('contactid').value = contactid;
        window.opener.$('addressid').value = addressid;
    }
    // Fill the object input with the current title
    if(title)
        window.opener.$('title').value = title;

    // Add the select with all the chrono number (in case there is more than one response project)
    if(listchrononumber){
        list_chrono_tr.style.display = 'table-row';
        list_chrono.innerHTML = listchrononumber;
        chrono_tr.style.display = 'none';
        window.opener.$('hiddenChronoNumber').value = hiddendata;
        window.opener.$('contact_id_tr').style.display = 'none';
    }

    // Display some usefull infos, fill res_id input and close the modal window
    window.opener.$('title_tr').style.display = 'table-row';
    window.opener.$('chrono_number').style.display = 'table-row';
    if(window.opener.$('close_incoming')){
        window.opener.$('close_incoming').style.display = 'table-row';
    }
    window.opener.$('res_id').value = res_id;

    self.close();
}

function fillHiddenInput(chrono_number){ // Function used to fill the chrono number and contact input when there is more than on response project
    document.getElementById('chrono_number').value = chrono_number;
    var data = document.getElementById('hiddenChronoNumber').value;
    var infos = data.split(',');
    var allInfos = [];
    for(var i = 0; i < infos.length; i++){  // Create the tab with chrono number and contact informations
        allInfos[i] = infos[i].split('#');
        if(allInfos[i][0] === chrono_number){   // Fill the hidden contact input
            if(allInfos[i][1] === '' && allInfos[i][2] === ''){
                document.getElementById('title').value = allInfos[i][3];
            }else{
                document.getElementById('contactid').value = allInfos[i][1];
                document.getElementById('addressid').value = allInfos[i][2];
                document.getElementById('title').value = allInfos[i][3];
                document.getElementById('contact_id_tr').style.display = 'none';
            }
        }
    }
}

function activePjTab(target) {

    $j('[id^=PjDocument_],#MainDocument').css('background-color', 'rgb(197, 197, 197)');
    $j('[id^=PjDocument_],#MainDocument').css('height', '21px');
    $j('[id^=iframePjDocument_],#iframeMainDocument').hide();

    $j('#'+target.id).css('background-color', 'white');
    $j('#'+target.id).css('height', '23px');
    $j('#iframe'+target.id).show();
}
