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
var http_1 = require("@angular/http");
var router_1 = require("@angular/router");
require("rxjs/add/operator/map");
var UserAdministrationComponent = (function () {
    function UserAdministrationComponent(http, route, zone) {
        var _this = this;
        this.http = http;
        this.route = route;
        this.zone = zone;
        this.user = {
            lang: {}
        };
        this.signatureModel = {
            base64: "",
            base64ForJs: "",
            name: "",
            type: "",
            size: 0,
            label: "",
        };
        this.userAbsenceModel = [];
        this.selectedSignature = -1;
        this.selectedSignatureLabel = "";
        this.resultInfo = "";
        this.loading = false;
        window['angularUserAdministrationComponent'] = {
            componentAfterUpload: function (base64Content) { return _this.processAfterUpload(base64Content); },
        };
    }
    UserAdministrationComponent.prototype.updateBreadcrumb = function (applicationName) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/users\"' style='cursor: pointer'>Utilisateurs</a> > Modification";
        }
    };
    UserAdministrationComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.route.params.subscribe(function (params) {
            _this.userId = params['userId'];
            _this.http.get(_this.coreUrl + 'rest/administration/users/' + _this.userId)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user = data;
                _this.loading = false;
                setTimeout(function () {
                    $j("#absenceUser").typeahead({
                        order: "asc",
                        display: "formattedUser",
                        templateValue: "{{user_id}}",
                        source: {
                            ajax: {
                                type: "GET",
                                dataType: "json",
                                url: _this.coreUrl + "rest/users/autocompleter",
                            }
                        }
                    });
                }, 0);
            }, function () {
                location.href = "index.php";
            });
        });
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
            this.resultInfo = "Taille maximum de fichier dépassée (2 MB)";
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        }
    };
    UserAdministrationComponent.prototype.clickOnUploader = function (id) {
        $j('#' + id).click();
    };
    UserAdministrationComponent.prototype.uploadSignatureTrigger = function (fileInput) {
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
            this.http.put(this.coreUrl + "rest/users/" + this.userId + "/password", {})
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }, function (err) {
                _this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            });
        }
    };
    UserAdministrationComponent.prototype.addGroup = function () {
        var _this = this;
        var index = $j("#groupsSelect option:selected").index();
        if (index > 0) {
            var group = {
                "groupId": this.user.allGroups[index - 1].group_id,
                "role": $j("#groupRole")[0].value
            };
            this.http.post(this.coreUrl + "rest/users/" + this.userId + "/groups", group)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user.groups = data.groups;
                _this.user.allGroups = data.allGroups;
                _this.resultInfo = data.success;
                $j("#groupRole")[0].value = "";
                $j('#addGroupModal').modal('hide');
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }, function (err) {
                _this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            });
        }
    };
    UserAdministrationComponent.prototype.updateGroup = function (group) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/groups/" + group.group_id, group)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.resultInfo = data.success;
            $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        }, function (err) {
            _this.resultInfo = JSON.parse(err._body).errors;
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    UserAdministrationComponent.prototype.deleteGroup = function (group) {
        var _this = this;
        var r = confirm('Voulez-vous vraiment retirer l\'utilisateur de ce groupe ?');
        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/groups/" + group.group_id)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user.groups = data.groups;
                _this.user.allGroups = data.allGroups;
                _this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }, function (err) {
                _this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            });
        }
    };
    UserAdministrationComponent.prototype.addEntity = function () {
        var _this = this;
        var index = $j("#entitiesSelect option:selected").index();
        if (index > 0) {
            var entity = {
                "entityId": this.user.allEntities[index - 1].entity_id,
                "role": $j("#entityRole")[0].value
            };
            this.http.post(this.coreUrl + "rest/users/" + this.userId + "/entities", entity)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user.entities = data.entities;
                _this.user.allEntities = data.allEntities;
                _this.resultInfo = data.success;
                $j("#entityRole")[0].value = "";
                $j('#addEntityModal').modal('hide');
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }, function (err) {
                _this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            });
        }
    };
    UserAdministrationComponent.prototype.updateEntity = function (entity) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id, entity)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.resultInfo = data.success;
            $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        }, function (err) {
            _this.resultInfo = JSON.parse(err._body).errors;
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    UserAdministrationComponent.prototype.updatePrimaryEntity = function (entity) {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id + "/primaryEntity", {})
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.user['entities'] = data.entities;
            _this.resultInfo = data.success;
            $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        }, function (err) {
            _this.resultInfo = JSON.parse(err._body).errors;
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    UserAdministrationComponent.prototype.deleteEntity = function (entity) {
        var _this = this;
        var r = confirm('Voulez-vous vraiment retirer l\'utilisateur de cette entité ?');
        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/entities/" + entity.entity_id)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user.entities = data.entities;
                _this.user.allEntities = data.allEntities;
                _this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }, function (err) {
                _this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            });
        }
    };
    UserAdministrationComponent.prototype.submitSignature = function () {
        var _this = this;
        this.http.post(this.coreUrl + "rest/users/" + this.userId + "/signature", this.signatureModel)
            .map(function (res) { return res.json(); })
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
            _this.resultInfo = data.success;
            $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        }, function (err) {
            _this.resultInfo = JSON.parse(err._body).errors;
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    UserAdministrationComponent.prototype.updateSignature = function () {
        var _this = this;
        var id = this.user.signatures[this.selectedSignature].id;
        this.http.put(this.coreUrl + "rest/users/" + this.userId + "/signature/" + id, { "label": this.selectedSignatureLabel })
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.user.signatures[_this.selectedSignature].signature_label = data.signature.signature_label;
            _this.selectedSignature = -1;
            _this.selectedSignatureLabel = "";
            _this.resultInfo = data.success;
            $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        }, function (err) {
            _this.resultInfo = JSON.parse(err._body).errors;
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    UserAdministrationComponent.prototype.deleteSignature = function (id) {
        var _this = this;
        var r = confirm('Voulez-vous vraiment supprimer la signature ?');
        if (r) {
            this.http.delete(this.coreUrl + "rest/users/" + this.userId + "/signature/" + id)
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.user.signatures = data.signatures;
                _this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }, function (err) {
                _this.resultInfo = JSON.parse(err._body).errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            });
        }
    };
    UserAdministrationComponent.prototype.addBasketRedirection = function () {
        var index = $j("#selectBasketAbsenceUser option:selected").index();
        if (index > 0 && $j("#absenceUser")[0].value != "") {
            this.userAbsenceModel.push({
                "basketId": this.user.baskets[index - 1].basket_id,
                "basketName": this.user.baskets[index - 1].basket_name,
                "virtual": this.user.baskets[index - 1].is_virtual,
                "basketOwner": this.user.baskets[index - 1].basket_owner,
                "newUser": $j("#absenceUser")[0].value,
                "index": index - 1
            });
            this.user.baskets[index - 1].disabled = true;
            $j('#selectBasketAbsenceUser option:eq(0)').prop("selected", true);
            $j("#absenceUser")[0].value = "";
        }
    };
    UserAdministrationComponent.prototype.delBasketRedirection = function (index) {
        this.user.baskets[this.userAbsenceModel[index].index].disabled = false;
        this.userAbsenceModel.splice(index, 1);
    };
    UserAdministrationComponent.prototype.activateAbsence = function () {
        var _this = this;
        this.http.post(this.coreUrl + "rest/users/" + this.userId + "/baskets/absence", this.userAbsenceModel)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.user.status = data.user.status;
            _this.userAbsenceModel = [];
            $j('#manageAbs').modal('hide');
            _this.resultInfo = data.success;
            $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        }, function (err) {
            _this.resultInfo = JSON.parse(err._body).errors;
            $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
            $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                $j("#resultInfo").slideUp(500);
            });
        });
    };
    UserAdministrationComponent.prototype.onSubmit = function () {
        var _this = this;
        this.http.put(this.coreUrl + "rest/users/" + this.userId, this.user)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.errors) {
                _this.resultInfo = data.errors;
                $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
            else {
                _this.resultInfo = data.success;
                $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                    $j("#resultInfo").slideUp(500);
                });
            }
        }, function (error) {
            alert(error.statusText);
        });
    };
    return UserAdministrationComponent;
}());
UserAdministrationComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals["user-administrationView"],
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css', 'css/user-administration.component.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, core_1.NgZone])
], UserAdministrationComponent);
exports.UserAdministrationComponent = UserAdministrationComponent;
