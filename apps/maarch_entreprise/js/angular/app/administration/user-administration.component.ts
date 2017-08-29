import { Component, OnInit, NgZone } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any) : any;

declare const angularGlobals : any;


@Component({
    templateUrl : angularGlobals["user-administrationView"],
    styleUrls   : ['css/user-administration.component.css'],
    providers   : [NotificationService]
})
export class UserAdministrationComponent implements OnInit {

    coreUrl                     : string;
    lang                        : any       = LANG;
    _search                     : string    = '';
    
    userId                      : string;
    serialId                    : number;
    creationMode                : boolean;

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
    userList                    : any[]     = [];
    
    selectedSignature           : number    = -1;
    selectedSignatureLabel      : string    = "";
    loading                     : boolean   = false;

    constructor(public http: HttpClient, private route: ActivatedRoute, private router: Router, private zone: NgZone, private notify: NotificationService) {
        window['angularUserAdministrationComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
        
    }

    updateBreadcrumb(applicationName: string) {
        var breadCrumb = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>"+this.lang.administration+"</a> > <a onclick='location.hash = \"/administration/users\"' style='cursor: pointer'>"+this.lang.users+"</a> > ";

        if(this.creationMode == true){
            breadCrumb += this.lang.userCreation;
        } else {
            breadCrumb += this.lang.userModification;
        }
        $j('#ariane')[0].innerHTML = breadCrumb;
    }

    ngOnInit(): void {
        //$j('#header').remove();
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.loading = false;
                this.updateBreadcrumb(angularGlobals.applicationName);
            } else {
                this.creationMode = false;
                this.serialId = params['id'];
                this.http.get(this.coreUrl + "rest/users/" + this.serialId + "/details")
                    .subscribe((data : any) => {
                        this.user = data;
                        this.userId = data.user_id;
                        this.updateBreadcrumb(angularGlobals.applicationName);
                        this.loading = false;

                        
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    toogleRedirect(basket:any) {
        $j('#redirectUser_'+basket.group_id+'_'+basket.basket_id).toggle();

        this.http.get(this.coreUrl + 'rest/administration/users')
        .subscribe((data : any) => {
            this.userList = data['users'];

        }, () => {
            location.href = "index.php";
        });
    }

    initService(){
        if($j('.jstree-container-ul').length == 0){
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
                "plugins" : [ "checkbox", "search" ] 
            });
            $j('#jstree')
            // listen for event
            .on('select_node.jstree', (e:any, data:any) => {
                this.addEntity(data.node.id);
            }).on('deselect_node.jstree', (e:any, data:any) => {
                console.log(data.node.id);
                this.deleteEntity(data.node.id);
            })
            // create the instance
            .jstree();

            var to : any = false;
            $j('#jstree_search').keyup(function () {
              if(to) { clearTimeout(to); }
              to = setTimeout(function () {
                var v = $j('#jstree_search').val();
                $j('#jstree').jstree(true).search(v);
              }, 250);
            });
        }
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

            this.notify.error("Taille maximum de fichier dépassée (2 MB)");
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

    resetPassword(user:any) {
        let r = confirm(this.lang.confirmAction+' '+this.lang.resetPsw);

        if (r) {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/password", {})
                .subscribe((data : any) => {
                    this.notify.success(this.lang.pswReseted+' '+this.lang.for+' « '+user.user_id+' »');
                }, (err) => {
                    this.notify.error(err.error.errors);
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
                this.notify.success(this.lang.groupAdded+' « '+group.group_id+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }else{
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id)
            .subscribe((data : any) => {
                this.user.groups = data.groups;
                this.user.allGroups = data.allGroups;
                this.notify.success(this.lang.groupDeleted+' « '+group.group_id+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }
    }
    updateGroup(group: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id, group)
            .subscribe((data : any) => {
                this.notify.success(this.lang.groupUpdated+' « '+group.group_id+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
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
                this.notify.success(this.lang.entityAdded+' « '+entiyId+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        
    }

    updateEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id, entity)
            .subscribe((data : any) => {
                this.notify.success(this.lang.entityUpdated+' « '+entity.entity_id+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updatePrimaryEntity(entity: any) {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .subscribe((data : any) => {
                this.user['entities'] = data.entities;
                this.notify.success(this.lang.entityTooglePrimary+' « '+entity.entity_id+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteEntity(entityId: any) {

        this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entityId)
            .subscribe((data : any) => {
                this.user.entities = data.entities;
                this.user.allEntities = data.allEntities;
                this.notify.success(this.lang.entityDeleted+' « '+entityId+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        
    }

    submitSignature() {
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/signatures", this.signatureModel)
            .subscribe((data : any) => {
                this.user.signatures = data.signatures;
                this.notify.success(this.lang.signAdded+' « '+this.signatureModel.name+' »');
                this.signatureModel  = {
                    base64                  : "",
                    base64ForJs             : "",
                    name                    : "",
                    type                    : "",
                    size                    : 0,
                    label                   : "",
                };
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    updateSignature(selectedSignature:any) {
        var id = this.user.signatures[selectedSignature].id;
        var label = this.user.signatures[selectedSignature].signature_label;
        
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id, {"label" : label})
            .subscribe((data : any) => {
                this.user.signatures[selectedSignature].signature_label = data.signature.signature_label;
                this.notify.success(this.lang.signUpdated+' « '+data.signature.signature_label+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    deleteSignature(signature: any) {
        let r = confirm(this.lang.confirmAction+' '+this.lang.delete+' « '+signature.signature_label+' »');

        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + signature.id)
                .subscribe((data : any) => {
                    this.user.signatures = data.signatures;
                    this.notify.success(this.lang.signDeleted+' « '+signature.signature_label+' »');
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    addBasketRedirection(i:number,basket:any) {
        let r = false;
        if(this.user.status != 'ABS'){
            let r = confirm(this.lang.confirmAction+' '+this.lang.activateAbs);
        }
        
        if (r || this.user.status == 'ABS') {
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
                this.notify.success(this.lang.absOn+' '+this.lang.for+' « '+this.user.user_id+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    desactivateAbsence() {
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/status", {"status" : "OK"})
                .subscribe((data : any) => {
                this.user.status = data.user.status;
                for (let i in this.user.baskets) {
                    this.user.baskets[i].userToDisplay = '';
                }
                this.notify.success(this.lang.absOff+' '+this.lang.for+' « '+this.user.user_id+' »');
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    onSubmit() {
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/users", this.user)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.userAdded+' « '+data.user.user_id+' »');
                    this.router.navigate(["/administration/users/" + data.user.id]);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId, this.user)
                .subscribe((data : any) => {
                    this.notify.success(this.lang.userUpdated+' « '+this.user.user_id+' »');
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }
}
