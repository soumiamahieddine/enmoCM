import { Component, OnInit, NgZone, ViewChild, ElementRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare const angularGlobals : any;


@Component({
    templateUrl : angularGlobals["user-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css','css/user-administration.component.css','../../node_modules/jstree-bootstrap-theme/dist/themes/proton/style.css']
})
export class UserAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;
    userId                      : string;
    serialId                    : number;
    userCreation                : boolean;

    user                        : any       = {};
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


    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private zone: NgZone) {
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
        //$j('#header').remove();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.userCreation = true;
                this.loading = false;
            } else {
                this.userCreation = false;
                this.serialId = params['id'];
                this.http.get(this.coreUrl + "rest/users/" + this.serialId + "/details")
                    .subscribe((data : any) => {
                        this.user = data;
                        this.userId = data.user_id;

                        this.loading = false;
                        
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    toogleRedirect(basket:any) {
        $j('#redirectUser_'+basket.group_id+'_'+basket.basket_id).toggle();
        $j('#absenceUser_'+basket.group_id+'_'+basket.basket_id).typeahead({
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
    }

    initService(){
        $j('#jstree').jstree({ 
            "checkbox" : {
                "three_state" : false //no cascade selection
            },
            'core' : {
                'themes': {
                    'name': 'proton',
                    'responsive': true
                },
                'data' : this.user.allEntities
            },
            "plugins" : [ "checkbox" ] 
        });
        $j('#jstree')
        // listen for event
        .on('select_node.jstree', (e:any, data:any) => {
            this.addEntity(data.node.id);
        }).on('deselect_node.jstree', (e:any, data:any) => {
            this.deleteEntity(data.node.id);
        })
        // create the instance
        .jstree();
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

            reader.onload = (value: any) => {
                window['angularUserAdministrationComponent'].componentAfterUpload(value.target.result);
                this.submitSignature();
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
                .subscribe((data : any) => {
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(err.error.errors);
                });
        }
    }

    toggleGroup(group: any){
        if($j('#'+group.group_id+'-input').is(':checked') == true){
            var groupReq = {
                "groupId"   : group.group_id,
                "role"      : group.role
            };
            this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/groups", groupReq)
            .subscribe((data : any) => {
                this.user.groups = data.groups;
                this.user.allGroups = data.allGroups;
                this.user.baskets = data.baskets;
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
        }else{
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id)
            .subscribe((data : any) => {
                this.user.groups = data.groups;
                this.user.allGroups = data.allGroups;
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
        }
    }
    updateGroup(group: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id, group)
            .subscribe((data : any) => {
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    deleteGroup(group: any) {
        let r = confirm('Voulez-vous vraiment retirer l\'utilisateur de ce groupe ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id)
                .subscribe((data : any) => {
                    this.user.groups = data.groups;
                    this.user.allGroups = data.allGroups;
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(err.error.errors);
                });
        }
    }

    addEntity(entiyId:any) {

        var entity = {
            "entityId"   : entiyId,
            "role"      : ''
        };

        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/entities", entity)
            .subscribe((data : any) => {
                this.user.entities = data.entities;
                this.user.allEntities = data.allEntities;

                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
        
    }

    updateEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id, entity)
            .subscribe((data : any) => {
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    updatePrimaryEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .subscribe((data : any) => {
                this.user['entities'] = data.entities;
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    deleteEntity(entityId: any) {

        this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entityId)
            .subscribe((data : any) => {
                this.user.entities = data.entities;
                this.user.allEntities = data.allEntities;
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
        
    }

    submitSignature() {
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/signatures", this.signatureModel)
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

    updateSignature(selectedSignature:any) {
        var id = this.user.signatures[selectedSignature].id;
        var label = this.user.signatures[selectedSignature].signature_label;
        
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id, {"label" : label})
            .subscribe((data : any) => {
                this.user.signatures[selectedSignature].signature_label = data.signature.signature_label;
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    deleteSignature(id: number) {
        let r = confirm('Voulez-vous vraiment supprimer la signature ?');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id)
                .subscribe((data : any) => {
                    this.user.signatures = data.signatures;
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(err.error.errors);
                });
        }
    }

    addBasketRedirection(i:number,basket:any) {
        let r = false;
        if(this.user.status != 'ABS'){
            r = confirm('Cela activera automatiquement le mode absent, continuer ?');
        }
        
        if (r || this.user.status == 'ABS') {
            this.user.baskets[i].userToDisplay = $j('#absenceUser_'+basket.group_id+'_'+basket.basket_id)[0].value;
            this.userAbsenceModel.push({
                "basketId"      : this.user.baskets[i].basket_id,
                "basketName"    : this.user.baskets[i].basket_name,
                "virtual"       : this.user.baskets[i].is_virtual,
                "basketOwner"   : this.user.baskets[i].basket_owner,
                "newUser"       : this.user.baskets[i].userToDisplay
            });
            this.activateAbsence();
        }
    }

    delBasketRedirection(i:number) {
        this.user.baskets[i].userToDisplay = '';
    }

    activateAbsence() {
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/baskets/absence", this.userAbsenceModel)
            .subscribe((data : any) => {
                this.user.status = data.user.status;
                this.userAbsenceModel  = [];
                successNotification(data.success);
            }, (err) => {
                errorNotification(err.error.errors);
            });
    }

    desactivateAbsence() {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/status", {"status" : "OK"})
                .subscribe((data : any) => {
                this.user.status = data.user.status;
                for (let i in this.user.baskets) {
                    this.user.baskets[i].userToDisplay = '';
                }
                successNotification(data.success);
            }, (err) => {
                    errorNotification(err.error.errors);
            });
    }

    onSubmit() {
        if (this.userCreation) {
            this.http.post(this.coreUrl + "rest/users", this.user)
                .subscribe((data : any) => {
                    successNotification(data.success);
                    this.router.navigate(["/administration/users/" + data.user.id]);
                }, (err) => {
                    errorNotification(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId, this.user)
                .subscribe((data : any) => {
                    successNotification(data.success);
                }, (err) => {
                    errorNotification(err.error.errors);
                });
        }
    }
}
