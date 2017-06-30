import { Component, OnInit, NgZone } from '@angular/core';
import { Http } from '@angular/http';
import { ActivatedRoute } from '@angular/router';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["user-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/user-administration.component.css']
})
export class UserAdministrationComponent implements OnInit {

    coreUrl                     : string;
    userId                      : string;

    user                        : any       = {
        lang                    : {}
    };
    signatureModel              : any       = {
        base64                  : "",
        base64ForJs             : "",
        name                    : "",
        type                    : "",
        size                    : 0,
        label                   : "",
    };
    userAbsenceModel            : any[]     = [];

    selectedSignature           : number    = -1;
    selectedSignatureLabel      : string    = "";
    resultInfo                  : string    = "";
    loading                     : boolean   = false;


    constructor(public http: Http, private route: ActivatedRoute, private zone: NgZone) {
        window['angularUserAdministrationComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/users\"' style='cursor: pointer'>Utilisateurs</a> > Modification";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            this.userId = params['userId'];

            this.http.get(this.coreUrl + 'rest/administration/users/' + this.userId)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user = data;

                    this.loading = false;
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
                }, () => {
                    location.href = "index.php";
                });
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

            this.resultInfo = "Taille maximum de fichier dépassée (2 MB)";
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                $j("#resultInfo").slideUp(500);
            });
        }
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
                window['angularUserAdministrationComponent'].componentAfterUpload(value.target.result);
            };

        }
    }

    displaySignatureEditionForm(index: number) {
        this.selectedSignature = index;
        this.selectedSignatureLabel = this.user.signatures[index].signature_label;
    }

    resetPassword() {
        let r = confirm('Voulez-vous vraiment réinitialiser le mot de passe de l\'utilisateur ?');

        if (r) {
            this.http.put(this.coreUrl + "rest/users/" + this.userId + "/password", {})
                .map(res => res.json())
                .subscribe((data) => {
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }

    addGroup() {
        var index = $j("#groupsSelect option:selected").index();

        if (index > 0) {
            var group = {
                "groupId"   : this.user.allGroups[index - 1].group_id,
                "role"      : $j("#groupRole")[0].value
            };

            this.http.post(this.coreUrl + "rest/users/" + this.userId + "/groups", group)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.groups = data.groups;
                    this.user.allGroups = data.allGroups;
                    this.resultInfo = data.success;
                    $j("#groupRole")[0].value = "";
                    $j('#addGroupModal').modal('hide');
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }

    updateGroup(group: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/groups/" + group.group_id, group)
            .map(res => res.json())
            .subscribe((data) => {
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    deleteGroup(group: any) {
        let r = confirm('Voulez-vous vraiment retirer l\'utilisateur de ce groupe ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/groups/" + group.group_id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.groups = data.groups;
                    this.user.allGroups = data.allGroups;
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }

    addEntity() {
        var index = $j("#entitiesSelect option:selected").index();

        if (index > 0) {
            var entity = {
                "entityId"   : this.user.allEntities[index - 1].entity_id,
                "role"      : $j("#entityRole")[0].value
            };

            this.http.post(this.coreUrl + "rest/users/" + this.userId + "/entities", entity)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.entities = data.entities;
                    this.user.allEntities = data.allEntities;
                    this.resultInfo = data.success;
                    $j("#entityRole")[0].value = "";
                    $j('#addEntityModal').modal('hide');
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }

    updateEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id, entity)
            .map(res => res.json())
            .subscribe((data) => {
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    updatePrimaryEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .map(res => res.json())
            .subscribe((data) => {
                this.user['entities'] = data.entities;
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });

            });
    }

    deleteEntity(entity: any) {
        let r = confirm('Voulez-vous vraiment retirer l\'utilisateur de cette entité ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.entities = data.entities;
                    this.user.allEntities = data.allEntities;
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function() {
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function() {
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }

    submitSignature() {
        this.http.post(this.coreUrl + "rest/users/" + this.userId + "/signature", this.signatureModel)
            .map(res => res.json())
            .subscribe((data) => {
                this.user.signatures = data.signatures;
                this.signatureModel  = {
                    base64                  : "",
                    base64ForJs             : "",
                    name                    : "",
                    type                    : "",
                    size                    : 0,
                    label                   : "",
                };
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function() {
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function() {
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    updateSignature() {
        var id = this.user.signatures[this.selectedSignature].id;

        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/signature/" + id, {"label" : this.selectedSignatureLabel})
            .map(res => res.json())
            .subscribe((data) => {
                this.user.signatures[this.selectedSignature].signature_label = data.signature.signature_label;
                this.selectedSignature = -1;
                this.selectedSignatureLabel = "";
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function() {
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    deleteSignature(id: number) {
        let r = confirm('Voulez-vous vraiment supprimer la signature ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/signature/" + id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.signatures = data.signatures;
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }, (err) => {
                    this.resultInfo = JSON.parse(err._body).errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function() {
                        $j("#resultInfo").slideUp(500);
                    });
                });
        }
    }

    addBasketRedirection() {
        var index = $j("#selectBasketAbsenceUser option:selected").index();

        if (index > 0 && $j("#absenceUser")[0].value != "") {
            this.userAbsenceModel.push({
                "basketId"      : this.user.baskets[index - 1].basket_id,
                "basketName"    : this.user.baskets[index - 1].basket_name,
                "virtual"       : this.user.baskets[index - 1].is_virtual,
                "basketOwner"   : this.user.baskets[index - 1].basket_owner,
                "newUser"       : $j("#absenceUser")[0].value,
                "index"         : index - 1
            });
            this.user.baskets[index - 1].disabled = true;
            $j('#selectBasketAbsenceUser option:eq(0)').prop("selected", true);
            $j("#absenceUser")[0].value = "";
        }
    }

    delBasketRedirection(index: number) {
        this.user.baskets[this.userAbsenceModel[index].index].disabled = false;
        this.userAbsenceModel.splice(index, 1);
    }

    activateAbsence() {
        this.http.post(this.coreUrl + "rest/users/" + this.userId + "/baskets/absence", this.userAbsenceModel)
            .map(res => res.json())
            .subscribe((data) => {
                this.user.status = data.user.status;
                this.userAbsenceModel  = [];
                $j('#manageAbs').modal('hide');
                this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            }, (err) => {
                this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                    $j("#resultInfo").slideUp(500);
                });
            });
    }

    onSubmit() {
        this.http.put(this.coreUrl + "rest/users/" + this.userId, this.user)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    this.resultInfo = data.errors;
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });

                } else {
                    this.resultInfo = data.success;
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function(){
                        $j("#resultInfo").slideUp(500);
                    });
                }
            }, (error) => {
                alert(error.statusText);
            });
    }
}
