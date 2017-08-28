"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var http_1 = require("@angular/common/http");
var router_1 = require("@angular/router");
var translate_component_1 = require("../translate.component");
var notification_service_1 = require("../notification.service");
var UserAdministrationComponent = (function () {
    function UserAdministrationComponent(http, route, router, zone, notify) {
        var _this = this;
        this.http = http;
        this.route = route;
        this.router = router;
        this.zone = zone;
        this.notify = notify;
        this.lang = translate_component_1.LANG;
        this.user = {};
        this.signatureModel = {
            base64: "",
            base64ForJs: "",
            name: "",
            type: "",
            size: 0,
            label: "",
        };
        this.userAbsenceModel = [];
        this.userList = [];
        this.selectedSignature = -1;
        this.selectedSignatureLabel = "";
        this.loading = false;
        window['angularUserAdministrationComponent'] = {
            componentAfterUpload: function (base64Content) { return _this.processAfterUpload(base64Content); },
        };
    }
    UserAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/users\"' style='cursor: pointer'>Utilisateurs</a>";
        }
    };
    UserAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        //$j('#header').remove();
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            if (typeof params['id'] == "undefined") {
                _this.userCreation = true;
                _this.loading = false;
            }
            else {
                _this.userCreation = false;
                _this.serialId = params['id'];
                _this.http.get(_this.coreUrl + "rest/users/" + _this.serialId + "/details")
                    .subscribe(function (data) {
                    _this.user = data;
                    _this.userId = data.user_id;
                    _this.loading = false;
                }, function () {
                    location.href = "index.php";
                });
            }
        });
    };
    UserAdministrationComponent.prototype.toogleRedirect = function (basket) {
        var _this = this;
        $j('#redirectUser_' + basket.group_id + '_' + basket.basket_id).toggle();
        this.http.get(this.coreUrl + 'rest/administration/users')
            .subscribe(function (data) {
            _this.userList = data['users'];
        }, function () {
            location.href = "index.php";
        });
    };
    UserAdministrationComponent.prototype.initService = function () {
        var _this = this;
        if ($j('.jstree-container-ul').length == 0) {
            $j('#jstree').jstree({
                "checkbox": {
                    "three_state": false //no cascade selection
                },
                'core': {
                    'themes': {
                        'name': 'proton',
                        'responsive': true
                    },
                    'data': this.user.allEntities
                },
                "plugins": ["checkbox", "search"]
            });
            $j('#jstree')
                .on('select_node.jstree', function (e, data) {
                _this.addEntity(data.node.id);
            }).on('deselect_node.jstree', function (e, data) {
                console.log(data.node.id);
                _this.deleteEntity(data.node.id);
            })
                .jstree();
            var to = false;
            $j('#jstree_search').keyup(function () {
                if (to) {
                    clearTimeout(to);
                }
                to = setTimeout(function () {
                    var v = $j('#jstree_search').val();
                    $j('#jstree').jstree(true).search(v);
                }, 250);
            });
        }
    };
    UserAdministrationComponent.prototype.processAfterUpload = function (b64Content) {
        var _this = this;
        this.zone.run(function () { return _this.resfreshUpload(b64Content); });
    };
    UserAdministrationComponent.prototype.resfreshUpload = function (b64Content) {
        if (this.signatureModel.size <= 2000000) {
            this.signatureModel.base64 = b64Content.replace(/^data:.*?;base64,/, "");
            this.signatureModel.base64ForJs = b64Content;
        }
        else {
            this.signatureModel.name = "";
            this.signatureModel.size = 0;
            this.signatureModel.type = "";
            this.signatureModel.base64 = "";
            this.signatureModel.base64ForJs = "";
            this.notify.error("Taille maximum de fichier dépassée (2 MB)");
        }
    };
    UserAdministrationComponent.prototype.clickOnUploader = function (id) {
        $j('#' + id).click();
    };
    UserAdministrationComponent.prototype.uploadSignatureTrigger = function (fileInput) {
        var _this = this;
        if (fileInput.target.files && fileInput.target.files[0]) {
            var reader = new FileReader();
            this.signatureModel.name = fileInput.target.files[0].name;
            this.signatureModel.size = fileInput.target.files[0].size;
            this.signatureModel.type = fileInput.target.files[0].type;
            if (this.signatureModel.label == "") {
                this.signatureModel.label = this.signatureModel.name;
            }
            reader.readAsDataURL(fileInput.target.files[0]);
            reader.onload = function (value) {
                window['angularUserAdministrationComponent'].componentAfterUpload(value.target.result);
                _this.submitSignature();
            };
        }
    };
    UserAdministrationComponent.prototype.displaySignatureEditionForm = function (index) {
        this.selectedSignature = index;
        this.selectedSignatureLabel = this.user.signatures[index].signature_label;
    };
    UserAdministrationComponent.prototype.resetPassword = function () {
        var _this = this;
        var r = confirm('Voulez-vous vraiment réinitialiser le mot de passe de l\'utilisateur ?');
        if (r) {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/password", {})
                .subscribe(function (data) {
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    UserAdministrationComponent.prototype.toggleGroup = function (group) {
        var _this = this;
        if ($j('#' + group.group_id + '-input').is(':checked') == true) {
            var groupReq = {
                "groupId": group.group_id,
                "role": group.role
            };
            this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/groups", groupReq)
                .subscribe(function (data) {
                _this.user.groups = data.groups;
                _this.user.allGroups = data.allGroups;
                _this.user.baskets = data.baskets;
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id)
                .subscribe(function (data) {
                _this.user.groups = data.groups;
                _this.user.allGroups = data.allGroups;
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    UserAdministrationComponent.prototype.updateGroup = function (group) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id, group)
            .subscribe(function (data) {
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.deleteGroup = function (group) {
        var _this = this;
        var r = confirm('Voulez-vous vraiment retirer l\'utilisateur de ce groupe ?');
        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/groups/" + group.group_id)
                .subscribe(function (data) {
                _this.user.groups = data.groups;
                _this.user.allGroups = data.allGroups;
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    UserAdministrationComponent.prototype.addEntity = function (entiyId) {
        var _this = this;
        var entity = {
            "entityId": entiyId,
            "role": ''
        };
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/entities", entity)
            .subscribe(function (data) {
            _this.user.entities = data.entities;
            _this.user.allEntities = data.allEntities;
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.updateEntity = function (entity) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id, entity)
            .subscribe(function (data) {
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.updatePrimaryEntity = function (entity) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .subscribe(function (data) {
            _this.user['entities'] = data.entities;
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.deleteEntity = function (entityId) {
        var _this = this;
        this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/entities/" + entityId)
            .subscribe(function (data) {
            _this.user.entities = data.entities;
            _this.user.allEntities = data.allEntities;
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.submitSignature = function () {
        var _this = this;
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/signatures", this.signatureModel)
            .subscribe(function (data) {
            _this.user.signatures = data.signatures;
            _this.signatureModel = {
                base64: "",
                base64ForJs: "",
                name: "",
                type: "",
                size: 0,
                label: "",
            };
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.updateSignature = function (selectedSignature) {
        var _this = this;
        var id = this.user.signatures[selectedSignature].id;
        var label = this.user.signatures[selectedSignature].signature_label;
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id, { "label": label })
            .subscribe(function (data) {
            _this.user.signatures[selectedSignature].signature_label = data.signature.signature_label;
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.deleteSignature = function (id) {
        var _this = this;
        var r = confirm('Voulez-vous vraiment supprimer la signature ?');
        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.serialId + "/signatures/" + id)
                .subscribe(function (data) {
                _this.user.signatures = data.signatures;
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    UserAdministrationComponent.prototype.addBasketRedirection = function (i, basket) {
        var r = false;
        if (this.user.status != 'ABS') {
            r = confirm('Cela activera automatiquement le mode absent, continuer ?');
        }
        if (r || this.user.status == 'ABS') {
            this.userAbsenceModel.push({
                "basketId": this.user.baskets[i].basket_id,
                "basketName": this.user.baskets[i].basket_name,
                "virtual": this.user.baskets[i].is_virtual,
                "basketOwner": this.user.baskets[i].basket_owner,
                "newUser": this.user.baskets[i].userToDisplay
            });
            this.activateAbsence();
        }
    };
    UserAdministrationComponent.prototype.delBasketRedirection = function (i) {
        this.user.baskets[i].userToDisplay = '';
    };
    UserAdministrationComponent.prototype.activateAbsence = function () {
        var _this = this;
        this.http.post(this.coreUrl + "rest/users/" + this.serialId + "/baskets/absence", this.userAbsenceModel)
            .subscribe(function (data) {
            _this.user.status = data.user.status;
            _this.userAbsenceModel = [];
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.desactivateAbsence = function () {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.serialId + "/status", { "status": "OK" })
            .subscribe(function (data) {
            _this.user.status = data.user.status;
            for (var i in _this.user.baskets) {
                _this.user.baskets[i].userToDisplay = '';
            }
            _this.notify.success(data.success);
        }, function (err) {
            _this.notify.error(err.error.errors);
        });
    };
    UserAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        if (this.userCreation) {
            this.http.post(this.coreUrl + "rest/users", this.user)
                .subscribe(function (data) {
                _this.notify.success(data.success);
                _this.router.navigate(["/administration/users/" + data.user.id]);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
        else {
            this.http.put(this.coreUrl + "rest/users/" + this.serialId, this.user)
                .subscribe(function (data) {
                _this.notify.success(data.success);
            }, function (err) {
                _this.notify.error(err.error.errors);
            });
        }
    };
    return UserAdministrationComponent;
}());
UserAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["user-administrationView"],
        styleUrls: ['css/user-administration.component.css'],
        providers: [notification_service_1.NotificationService]
    }),
    __metadata("design:paramtypes", [http_1.HttpClient, router_1.ActivatedRoute, router_1.Router, core_1.NgZone, notification_service_1.NotificationService])
], UserAdministrationComponent);
exports.UserAdministrationComponent = UserAdministrationComponent;
