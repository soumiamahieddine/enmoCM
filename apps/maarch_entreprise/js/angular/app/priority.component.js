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
require("rxjs/add/operator/map");
var router_1 = require("@angular/router");
var PriorityComponent = (function () {
    function PriorityComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.mode = null;
        this.priority = {
            id: null,
            label_priority: null,
            color_priority: '#ffffff',
            working_days: 'Y',
            delays: '*'
        };
    }
    PriorityComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.coreUrl = angularGlobals.coreUrl;
        this.preparePriority();
        this.route.params.subscribe(function (params) {
            if (_this.route.toString().includes('update')) {
                _this.mode = 'update';
                _this.priorityId = params['id'];
                _this.getPriorityInfos(_this.priorityId);
            }
            else if (_this.route.toString().includes('create')) {
                _this.mode = 'create';
            }
        });
    };
    PriorityComponent.prototype.preparePriority = function () {
        $j('#inner_content').remove();
    };
    PriorityComponent.prototype.getPriorityInfos = function (priorityId) {
        var _this = this;
        var intId = parseInt(priorityId);
        this.http.get(this.coreUrl + 'rest/priorities/' + intId)
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
                var infoPriority = data;
                _this.priority.id = infoPriority[0].id;
                _this.priority.label_priority = infoPriority[0].label_priority;
                _this.priority.color_priority = infoPriority[0].color_priority;
                _this.priority.working_days = infoPriority[0].working_days;
                _this.priority.delays = infoPriority[0].delays;
            }
        });
    };
    PriorityComponent.prototype.submitPriority = function () {
        var _this = this;
        if (this.mode == 'create') {
            this.http.post(this.coreUrl + 'rest/priorities', this.priority)
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
                    _this.resultInfo = 'Priorité créée avec succès';
                    $j('#resultInfo').removeClass().addClass('alert alert-danger alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.router.navigate(['administration/priorities']);
                }
            });
        }
        else if (this.mode == 'update') {
            this.http.put(this.coreUrl + 'rest/priorities/' + this.priorityId, this.priority)
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
                    _this.resultInfo = 'Priorité mise à jour avec succès';
                    $j('#resultInfo').removeClass().addClass('alert alert-success alert-dismissible');
                    $j("#resultInfo").fadeTo(3000, 500).slideUp(500, function () {
                        $j("#resultInfo").slideUp(500);
                    });
                    _this.router.navigate(['administration/priorities']);
                }
            });
        }
    };
    return PriorityComponent;
}());
PriorityComponent = __decorate([
    core_1.Component({
        templateUrl: angularGlobals.priorityView,
        styleUrls: ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
    }),
    __metadata("design:paramtypes", [http_1.Http, router_1.ActivatedRoute, router_1.Router])
], PriorityComponent);
exports.PriorityComponent = PriorityComponent;
