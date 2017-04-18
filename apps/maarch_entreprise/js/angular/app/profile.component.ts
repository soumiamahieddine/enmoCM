import { Component, OnInit, NgZone } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;
declare var tinymce : any;
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
    signatureModel              : any       = {
        base64                  : "",
        base64ForJs             : "",
        name                    : "",
        type                    : "",
        size                    : 0,
        label                   : "",
    };
    mailSignatureModel          : any       = {
        selected                : 0,
        htmlBody                : "",
        title                   : "",
    };

    showPassword                : boolean   = false;
    resultInfo                  : string    = "";
    loading                     : boolean   = false;


    constructor(public http: Http, private zone: NgZone) {
        window['angularProfileComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
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
        tinymce.baseURL = "tools/tiny_mce";
        tinymce.suffix = '.min';
        tinymce.init({
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

        });

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

    processAfterUpload(b64Content: any) {
        this.zone.run(() => this.resfreshUpload(b64Content));
    }

    resfreshUpload(b64Content: any) {
        this.signatureModel.base64 = b64Content.replace(/^data:.*?;base64,/, "");
        this.signatureModel.base64ForJs = b64Content;
    }

    displayPassword() {
        this.showPassword = !this.showPassword;
    }

    clickOnUploader(id: string) {
        $j('#' + id).click();
    }

    exitProfile() {
        location.hash = "";
        location.reload();
    }

    uploadSignatureTrigger(fileInput: any) {
        if (fileInput.target.files && fileInput.target.files[0]) {
            var reader = new FileReader();
            reader.readAsDataURL(fileInput.target.files[0]);

            reader.onload = function (value: any) {
                window['angularProfileComponent'].componentAfterUpload(value.target.result);
            };

            this.signatureModel.name = fileInput.target.files[0].name;
            this.signatureModel.size = fileInput.target.files[0].size;
            this.signatureModel.type = fileInput.target.files[0].type;
            if (this.signatureModel.label == "") {
                this.signatureModel.label = this.signatureModel.name;
            }
        }
    }

    changeEmailSignature() {
        var index = $j("#emailSignaturesSelect").prop("selectedIndex");
        this.mailSignatureModel.selected = index;

        if (index > 0) {
            tinymce.get('emailSignature').setContent(this.user.emailSignatures[index - 1].html_body);
            this.mailSignatureModel.title = this.user.emailSignatures[index - 1].title;
        } else {
            tinymce.get('emailSignature').setContent("");
            this.mailSignatureModel.title = "";
        }
    }

    updatePassword() {
        this.http.put(this.coreUrl + 'rest/currentUser/password', this.passwordModel)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                } else {
                    this.showPassword = false;
                    this.passwordModel = {
                        currentPassword         : "",
                        newPassword             : "",
                        reNewPassword           : "",
                    };
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    //auto close
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }
            });
    }

    submitEmailSignature() {
        this.mailSignatureModel.htmlBody = tinymce.get('emailSignature').getContent();

        this.http.post(this.coreUrl + 'rest/currentUser/emailSignature', this.mailSignatureModel)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    alert(data.errors);
                } else {
                    this.user.emailSignatures = data.emailSignatures;
                    this.mailSignatureModel     = {
                        selected                : 0,
                        htmlBody                : "",
                        title                   : "",
                    };
                    tinymce.get('emailSignature').setContent("");
                }
            });
    }

    updateEmailSignature() {
        this.mailSignatureModel.htmlBody = tinymce.get('emailSignature').getContent();
        var id = this.user.emailSignatures[this.mailSignatureModel.selected - 1].id;

        this.http.put(this.coreUrl + 'rest/currentUser/emailSignature/' + id, this.mailSignatureModel)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    alert(data.errors);
                } else {
                    this.user.emailSignatures[this.mailSignatureModel.selected - 1].title = data.emailSignature.title;
                    this.user.emailSignatures[this.mailSignatureModel.selected - 1].html_body = data.emailSignature.html_body;
                }
            });
    }

    deleteEmailSignature() {
        let r = confirm('Voulez-vous vraiment supprimer la signature de mail ?');

        if (r) {
            var id = this.user.emailSignatures[this.mailSignatureModel.selected - 1].id;

            this.http.delete(this.coreUrl + 'rest/currentUser/emailSignature/' + id)
                .map(res => res.json())
                .subscribe((data) => {
                    if (data.errors) {
                        alert(data.errors);
                    } else {
                        this.user.emailSignatures = data.emailSignatures;
                        this.mailSignatureModel     = {
                            selected                : 0,
                            htmlBody                : "",
                            title                   : "",
                        };
                        tinymce.get('emailSignature').setContent("");
                    }
                });
        }
    }

    submitSignature() {
        this.http.post(this.coreUrl + 'rest/currentUser/signature', this.signatureModel)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    alert(data.errors);
                } else {
                    this.user.signatures = data.signatures;
                    this.signatureModel  = {
                        base64                  : "",
                        base64ForJs             : "",
                        name                    : "",
                        type                    : "",
                        size                    : 0,
                        label                   : "",
                    };
                }
            });
    }

    deleteSignature(id: number) {
        let r = confirm('Voulez-vous vraiment supprimer la signature ?');

        if (r) {
            this.http.delete(this.coreUrl + 'rest/currentUser/signature/' + id)
                .map(res => res.json())
                .subscribe((data) => {
                    if (data.errors) {
                        alert(data.errors);
                    } else {
                        this.user.signatures = data.signatures;
                    }
                });
        }
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/user/profile', this.user)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                            
                }else{
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    //auto close
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });   
                }
            });
    }

}
