import { Component, OnInit, NgZone } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var tinymce : any;
declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals.profileView,
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/profile.component.css']
})
export class ProfileComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;

    user                        : any       = {
        baskets                 : []
    };
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
    userAbsenceModel            : any[]     = [];

    showPassword                : boolean   = false;
    selectedSignature           : number    = -1;
    selectedSignatureLabel      : string    = "";
    loading                     : boolean   = false;


    constructor(public http: HttpClient, private zone: NgZone) {
        window['angularProfileComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
    }

    prepareProfile() {
        $j('#inner_content').remove();
        $j('#menunav').hide();
        $j('#divList').remove();
        $j('#magicContactsTable').remove();
        $j('#manageBasketsOrderTable').remove();
        $j('#controlParamTechnicTable').remove();
        $j('#container').width("99%");
        if ($j('#content h1')[0] && $j('#content h1')[0] != $j('my-app h1')[0]) {
            $j('#content h1')[0].remove();
        }

        //LOAD EDITOR TINYMCE for MAIL SIGN
        tinymce.baseURL = "../../node_modules/tinymce";
        tinymce.suffix = '.min';
        tinymce.init({
            selector: "textarea#emailSignature",
            statusbar : false,
            language : "fr_FR",
            language_url: "tools/tinymce/langs/fr_FR.js",
            height : "200",
            plugins: [
                "textcolor"
            ],
            external_plugins: {
                'bdesk_photo': "../../apps/maarch_entreprise/tools/tinymce/bdesk_photo/plugin.min.js"
            },
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

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > Profil";
        }
    }

    ngOnInit(): void {
        this.prepareProfile();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/users/profile')
            .subscribe((data : any) => {
                this.user = data;

                setTimeout(() => {
                    $j("#absenceUser").typeahead({
                        order: "asc",
                        display: "formattedUser",
                        templateValue: "{{user_id}}",
                        source: {
                            ajax: {
                                type: "GET",
                                dataType: "json",
                                url: this.coreUrl + "rest/users/autocompleter",
                            }
                        }
                    });
                }, 0);

                this.loading = false;
            });
    }

    processAfterUpload(b64Content: any) {
        this.zone.run(() => this.resfreshUpload(b64Content));
    }

    resfreshUpload(b64Content: any) {
        if (this.signatureModel.size <= 2000000) {
            this.signatureModel.base64 = b64Content.replace(/^data:.*?;base64,/, "");
            this.signatureModel.base64ForJs = b64Content;
        } else {
            this.signatureModel.name = "";
            this.signatureModel.size = 0;
            this.signatureModel.type = "";
            this.signatureModel.base64 = "";
            this.signatureModel.base64ForJs = "";

            errorNotification("Taille maximum de fichier dépassée (2 MB)");
        }
    }

    displayPassword() {
        this.showPassword = !this.showPassword;
    }

    clickOnUploader(id: string) {
        $j('#' + id).click();
    }

    uploadSignatureTrigger(fileInput: any) {
        if (fileInput.target.files && fileInput.target.files[0]) {
            var reader = new FileReader();

            this.signatureModel.name = fileInput.target.files[0].name;
            this.signatureModel.size = fileInput.target.files[0].size;
            this.signatureModel.type = fileInput.target.files[0].type;
            if (this.signatureModel.label == "") {
                this.signatureModel.label = this.signatureModel.name;
            }

            reader.readAsDataURL(fileInput.target.files[0]);

            reader.onload = function (value: any) {
                window['angularProfileComponent'].componentAfterUpload(value.target.result);
            };

        }
    }

    displaySignatureEditionForm(index: number) {
        this.selectedSignature = index;
        this.selectedSignatureLabel = this.user.signatures[index].signature_label;
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

    addBasketRedirection() {
        var index = $j("#selectBasketAbsenceUser option:selected").index();

        if (index > 0) {
            this.userAbsenceModel.push({
                "basketId"      : this.user.baskets[index - 1].basket_id,
                "basketName"    : this.user.baskets[index - 1].basket_name,
                "virtual"       : this.user.baskets[index - 1].is_virtual,
                "basketOwner"   : this.user.baskets[index - 1].basket_owner,
                "newUser"       : $j("#absenceUser")[0].value,
                "index"         : index - 1
            });
            this.user.baskets[index - 1].disabled = true;
            $j('#selectBasketAbsenceUser option:eq(0)').prop('selected', true);
            $j("#absenceUser")[0].value = "";
        }
    }

    delBasketRedirection(index: number) {
        this.user.baskets[this.userAbsenceModel[index].index].disabled = false;
        this.userAbsenceModel.splice(index, 1);
    }

    activateAbsence() {
        this.http.post(this.coreUrl + "rest/users/" + this.user.user_id + "/baskets/absence", this.userAbsenceModel)
            .subscribe(() => {
                this.userAbsenceModel  = [];
                location.search = "?display=true&page=logout&abs_mode";
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    updatePassword() {
        this.http.put(this.coreUrl + 'rest/currentUser/password', this.passwordModel)
            .subscribe((data : any) => {
                this.showPassword = false;
                this.passwordModel = {
                    currentPassword         : "",
                    newPassword             : "",
                    reNewPassword           : "",
                };
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    submitEmailSignature() {
        this.mailSignatureModel.htmlBody = tinymce.get('emailSignature').getContent();

        this.http.post(this.coreUrl + 'rest/currentUser/emailSignature', this.mailSignatureModel)
            .subscribe((data : any) => {
                if (data.errors) {
                    errorNotification(data.errors);
                } else {
                    this.user.emailSignatures = data.emailSignatures;
                    this.mailSignatureModel     = {
                        selected                : 0,
                        htmlBody                : "",
                        title                   : "",
                    };
                    tinymce.get('emailSignature').setContent("");
                    successNotification(data.success);
                }
            });
    }

    updateEmailSignature() {
        this.mailSignatureModel.htmlBody = tinymce.get('emailSignature').getContent();
        var id = this.user.emailSignatures[this.mailSignatureModel.selected - 1].id;

        this.http.put(this.coreUrl + 'rest/currentUser/emailSignature/' + id, this.mailSignatureModel)
            .subscribe((data : any) => {
                if (data.errors) {
                    errorNotification(data.errors);
                } else {
                    this.user.emailSignatures[this.mailSignatureModel.selected - 1].title = data.emailSignature.title;
                    this.user.emailSignatures[this.mailSignatureModel.selected - 1].html_body = data.emailSignature.html_body;
                    successNotification(data.success);
                }
            });
    }

    deleteEmailSignature() {
        let r = confirm('Voulez-vous vraiment supprimer la signature de mail ?');

        if (r) {
            var id = this.user.emailSignatures[this.mailSignatureModel.selected - 1].id;

            this.http.delete(this.coreUrl + 'rest/currentUser/emailSignature/' + id)
                .subscribe((data : any) => {
                    if (data.errors) {
                        errorNotification(data.errors);
                    } else {
                        this.user.emailSignatures = data.emailSignatures;
                        this.mailSignatureModel     = {
                            selected                : 0,
                            htmlBody                : "",
                            title                   : "",
                        };
                        tinymce.get('emailSignature').setContent("");
                        successNotification(data.success);
                    }
                });
        }
    }

    submitSignature() {
        this.http.post(this.coreUrl + "rest/users/" + this.user.id + "/signatures", this.signatureModel)
            .subscribe((data : any) => {
                this.user.signatures = data.signatures;
                this.signatureModel  = {
                    base64                  : "",
                    base64ForJs             : "",
                    name                    : "",
                    type                    : "",
                    size                    : 0,
                    label                   : "",
                };
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    updateSignature() {
        var id = this.user.signatures[this.selectedSignature].id;

        this.http.put(this.coreUrl + "rest/users/" + this.user.id + "/signatures/" + id, {"label" : this.selectedSignatureLabel})
            .subscribe((data : any) => {
                this.user.signatures[this.selectedSignature].signature_label = data.signature.signature_label;
                this.selectedSignature = -1;
                this.selectedSignatureLabel = "";
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    deleteSignature(id: number) {
        let r = confirm('Voulez-vous vraiment supprimer la signature ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.user.id + "/signatures/" + id)
                .subscribe((data : any) => {
                    this.user.signatures = data.signatures;
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(err.error.errors);
                });
        }
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/users/profile', this.user)
            .subscribe((data : any) => {
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }
}
