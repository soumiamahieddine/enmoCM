import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;
declare var Prototype : any;
declare function disablePrototypeJS(method: string, plugins: any) : any;


@Component({
  templateUrl: 'js/angular/app/Views/profile.html',
})
export class ProfileComponent implements OnInit {

    coreUrl                     : string;

    user                        : any       = {};
    passwordModel               : any       = {
        currentPassword         : "",
        newPassword             : "",
        reNewPassword           : "",
    };
    
    showPassword                : boolean   = false;
    loading                     : boolean   = false;


    constructor(public http: Http) {
    }

    prepareProfile() {
        $j('#inner_content').remove();
        $j('#menunav').hide();
        $j('#container').width("99%");

        if (Prototype.BrowserFeatures.ElementExtensions) {
            //FIX PROTOTYPE CONFLICT
            let pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover','tab'];
            disablePrototypeJS('show', pluginsToDisable);
            disablePrototypeJS('hide', pluginsToDisable);
        }

        //LOAD EDITOR TINYMCE for MAIL SIGN
        /*tinymce.init({
            selector: "textarea#emailSignature",
            statusbar : false,
            language : "fr_FR",
            height : "120",
            plugins: [
                "textcolor bdesk_photo"
            ],
            menubar: false,
            toolbar: "undo | bold italic underline | alignleft aligncenter alignright | bdesk_photo | forecolor",
            theme_buttons1_add : "fontselect,fontsizeselect",
            theme_buttons2_add_before : "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
            theme_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
            theme_buttons3_add_before : "tablecontrols,separator",
            theme_buttons3_add : "separator,print,separator,ltr,rtl,separator,fullscreen,separator,insertlayer,moveforward,movebackward,absolut",
            theme_toolbar_align : "left",
            theme_advanced_toolbar_location : "top",
            theme_styles : "Header 1=header1;Header 2=header2;Header 3=header3;Table Row=tableRow1"
    
        });*/

    }

    ngOnInit(): void {
        this.prepareProfile();

        this.loading = true;

        this.http.get('index.php?display=true&page=initializeJsGlobalConfig')
            .map(res => res.json())
            .subscribe((data) => {
                this.coreUrl = data.coreurl;
                this.http.get(this.coreUrl + 'rest/user/profile')
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.user = data;

                        this.loading = false;
                    });
            });
    }

    displayPassword() {
        this.showPassword = !this.showPassword;
    }

    changePassword() {
        this.http.put(this.coreUrl + 'rest/user/password', this.passwordModel)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    alert(data.errors);
                } else {
                    this.showPassword = false;
                    this.passwordModel = {
                        currentPassword         : "",
                        newPassword             : "",
                        reNewPassword           : "",
                    };
                }
            });
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/user/profile', this.user)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    $j('#resultInfo').html(data.errors);
                    $j('#resultInfo').removeClass('hide').addClass('alert alert-danger alert-dismissible');
                    $j('#resultInfo').modal('show');
                            
                }else{
                    $j('#resultInfo').html('Les informations utilisateur ont été modifiés');
                    $j('#resultInfo').removeClass('hide').addClass('alert alert-success alert-dismissible');
                    //auto close
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });   
                }
            });
    }

}
