"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var router_1 = require("@angular/router");
var administration_component_1 = require("./administration.component");
var users_administration_component_1 = require("./users-administration.component");
var user_administration_component_1 = require("./user-administration.component");
var groups_administration_component_1 = require("./groups-administration.component");
var group_administration_component_1 = require("./group-administration.component");
var baskets_administration_component_1 = require("./baskets-administration.component");
var basket_administration_component_1 = require("./basket-administration.component");
var doctypes_administration_component_1 = require("./doctypes-administration.component");
var diffusionModels_administration_component_1 = require("./diffusionModels-administration.component");
var diffusionModel_administration_component_1 = require("./diffusionModel-administration.component");
var entities_administration_component_1 = require("./entities-administration.component");
var entity_administration_component_1 = require("./entity-administration.component");
var statuses_administration_component_1 = require("./statuses-administration.component");
var status_administration_component_1 = require("./status-administration.component");
var actions_administration_component_1 = require("./actions-administration.component");
var action_administration_component_1 = require("./action-administration.component");
var parameter_administration_component_1 = require("./parameter-administration.component");
var parameters_administration_component_1 = require("./parameters-administration.component");
var priorities_administration_component_1 = require("./priorities-administration.component");
var priority_administration_component_1 = require("./priority-administration.component");
var reports_administration_component_1 = require("./reports-administration.component");
var notifications_administration_component_1 = require("./notifications-administration.component");
var notification_administration_component_1 = require("./notification-administration.component");
var notifications_schedule_administration_component_1 = require("./notifications-schedule-administration.component");
var history_administration_component_1 = require("./history-administration.component");
var historyBatch_administration_component_1 = require("./historyBatch-administration.component");
var update_status_administration_component_1 = require("./update-status-administration.component");
var AdministrationRoutingModule = /** @class */ (function () {
    function AdministrationRoutingModule() {
    }
    AdministrationRoutingModule = __decorate([
        core_1.NgModule({
            imports: [
                router_1.RouterModule.forChild([
                    { path: 'administration', component: administration_component_1.AdministrationComponent },
                    { path: 'administration/users', component: users_administration_component_1.UsersAdministrationComponent },
                    { path: 'administration/users/new', component: user_administration_component_1.UserAdministrationComponent },
                    { path: 'administration/users/:id', component: user_administration_component_1.UserAdministrationComponent },
                    { path: 'administration/groups', component: groups_administration_component_1.GroupsAdministrationComponent },
                    { path: 'administration/groups/new', component: group_administration_component_1.GroupAdministrationComponent },
                    { path: 'administration/groups/:id', component: group_administration_component_1.GroupAdministrationComponent },
                    { path: 'administration/baskets', component: baskets_administration_component_1.BasketsAdministrationComponent },
                    { path: 'administration/baskets/new', component: basket_administration_component_1.BasketAdministrationComponent },
                    { path: 'administration/baskets/:id', component: basket_administration_component_1.BasketAdministrationComponent },
                    { path: 'administration/doctypes', component: doctypes_administration_component_1.DoctypesAdministrationComponent },
                    { path: 'administration/diffusionModels', component: diffusionModels_administration_component_1.DiffusionModelsAdministrationComponent },
                    { path: 'administration/diffusionModels/:id', component: diffusionModel_administration_component_1.DiffusionModelAdministrationComponent },
                    { path: 'administration/entities', component: entities_administration_component_1.EntitiesAdministrationComponent },
                    { path: 'administration/entities/new', component: entity_administration_component_1.EntityAdministrationComponent },
                    { path: 'administration/entities/:id', component: entity_administration_component_1.EntityAdministrationComponent },
                    { path: 'administration/statuses', component: statuses_administration_component_1.StatusesAdministrationComponent },
                    { path: 'administration/statuses/new', component: status_administration_component_1.StatusAdministrationComponent },
                    { path: 'administration/statuses/:identifier', component: status_administration_component_1.StatusAdministrationComponent },
                    { path: 'administration/parameters', component: parameters_administration_component_1.ParametersAdministrationComponent },
                    { path: 'administration/parameters/new', component: parameter_administration_component_1.ParameterAdministrationComponent },
                    { path: 'administration/parameters/:id', component: parameter_administration_component_1.ParameterAdministrationComponent },
                    { path: 'administration/reports', component: reports_administration_component_1.ReportsAdministrationComponent },
                    { path: 'administration/priorities', component: priorities_administration_component_1.PrioritiesAdministrationComponent },
                    { path: 'administration/priorities/new', component: priority_administration_component_1.PriorityAdministrationComponent },
                    { path: 'administration/priorities/:id', component: priority_administration_component_1.PriorityAdministrationComponent },
                    { path: 'administration/actions', component: actions_administration_component_1.ActionsAdministrationComponent },
                    { path: 'administration/actions/new', component: action_administration_component_1.ActionAdministrationComponent },
                    { path: 'administration/actions/:id', component: action_administration_component_1.ActionAdministrationComponent },
                    { path: 'administration/notifications', component: notifications_administration_component_1.NotificationsAdministrationComponent },
                    { path: 'administration/notifications/new', component: notification_administration_component_1.NotificationAdministrationComponent },
                    { path: 'administration/notifications/schedule', component: notifications_schedule_administration_component_1.NotificationsScheduleAdministrationComponent },
                    { path: 'administration/notifications/:identifier', component: notification_administration_component_1.NotificationAdministrationComponent },
                    { path: 'administration/history', component: history_administration_component_1.HistoryAdministrationComponent },
                    { path: 'administration/historyBatch', component: historyBatch_administration_component_1.HistoryBatchAdministrationComponent },
                    { path: 'administration/update-status', component: update_status_administration_component_1.UpdateStatusAdministrationComponent },
                ]),
            ],
            exports: [
                router_1.RouterModule
            ]
        })
    ], AdministrationRoutingModule);
    return AdministrationRoutingModule;
}());
exports.AdministrationRoutingModule = AdministrationRoutingModule;
