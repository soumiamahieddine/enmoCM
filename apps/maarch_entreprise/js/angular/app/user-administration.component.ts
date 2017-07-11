import { Component, OnInit, NgZone } from '@angular/core';
import { Http } from '@angular/http';
import { ActivatedRoute, Router } from '@angular/router';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["user-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/user-administration.component.css']
})
export class UserAdministrationComponent implements OnInit {

    coreUrl                     : string;
    userId                      : string;
    serialId                    : number;
    userCreation                : boolean;

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
    loading                     : boolean   = false;


    constructor(public http: Http, private route: ActivatedRoute, private router: Router, private zone: NgZone) {
        window['angularUserAdministrationComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/users\"' style='cursor: pointer'>Utilisateurs</a>";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.userCreation = true;
                this.http.get(this.coreUrl + "rest/administration/users/new")
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.user = data;

                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            } else {
                this.userCreation = false;
                this.serialId = params['id'];
                this.http.get(this.coreUrl + "rest/administration/users/" + this.serialId)
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.user = data;
                        this.userId = data.user_id;

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
            }
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
            this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/password", {})
                .map(res => res.json())
                .subscribe((data) => {
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
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

            this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/groups", group)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.groups = data.groups;
                    this.user.allGroups = data.allGroups;
                    $j("#groupRole")[0].value = "";
                    $j('#addGroupModal').modal('hide');
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }

    updateGroup(group: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id, group)
            .map(res => res.json())
            .subscribe((data) => {
                successNotification(data.success);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
    }

    deleteGroup(group: any) {
        let r = confirm('Voulez-vous vraiment retirer l\'utilisateur de ce groupe ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.groups = data.groups;
                    this.user.allGroups = data.allGroups;
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
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

            this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/entities", entity)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.entities = data.entities;
                    this.user.allEntities = data.allEntities;
                    $j("#entityRole")[0].value = "";
                    $j('#addEntityModal').modal('hide');
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }

    updateEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id, entity)
            .map(res => res.json())
            .subscribe((data) => {
                successNotification(data.success);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
    }

    updatePrimaryEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .map(res => res.json())
            .subscribe((data) => {
                this.user['entities'] = data.entities;
                successNotification(data.success);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
    }

    deleteEntity(entity: any) {
        let r = confirm('Voulez-vous vraiment retirer l\'utilisateur de cette entité ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.entities = data.entities;
                    this.user.allEntities = data.allEntities;
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }

    submitSignature() {
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/signatures", this.signatureModel)
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
                successNotification(data.success);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
    }

    updateSignature() {
        var id = this.user.signatures[this.selectedSignature].id;

        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id, {"label" : this.selectedSignatureLabel})
            .map(res => res.json())
            .subscribe((data) => {
                this.user.signatures[this.selectedSignature].signature_label = data.signature.signature_label;
                this.selectedSignature = -1;
                this.selectedSignatureLabel = "";
                successNotification(data.success);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
    }

    deleteSignature(id: number) {
        let r = confirm('Voulez-vous vraiment supprimer la signature ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id)
                .map(res => res.json())
                .subscribe((data) => {
                    this.user.signatures = data.signatures;
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
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
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/baskets/absence", this.userAbsenceModel)
            .map(res => res.json())
            .subscribe((data) => {
                this.user.status = data.user.status;
                this.userAbsenceModel  = [];
                $j('#manageAbs').modal('hide');
                successNotification(data.success);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
    }

    deactivateAbsence() {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/status", {"status" : "OK"})
            .map(res => res.json())
            .subscribe((data) => {
                this.user.status = data.user.status;
                successNotification(data.success);
            }, (err) => {
                errorNotification(JSON.parse(err._body).errors);
            });
    }

    onSubmit() {
        if (this.userCreation) {
            this.http.post(this.coreUrl + "rest/users", this.user)
                .map(res => res.json())
                .subscribe((data) => {
                    successNotification(data.success);
                    this.router.navigate(["/administration/users/" + data.user.id]);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId, this.user)
                .map(res => res.json())
                .subscribe((data) => {
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }
}
