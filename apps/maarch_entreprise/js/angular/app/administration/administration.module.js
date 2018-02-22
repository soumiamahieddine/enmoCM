"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@angular/core");
var common_1 = require("@angular/common");
var forms_1 = require("@angular/forms");
var http_1 = require("@angular/common/http");
var app_material_module_1 = require("../app-material.module");
var administration_routing_module_1 = require("./administration-routing.module");
var administration_component_1 = require("./administration.component");
var users_administration_component_1 = require("./users-administration.component");
var groups_administration_component_1 = require("./groups-administration.component");
var user_administration_component_1 = require("./user-administration.component");
var group_administration_component_1 = require("./group-administration.component");
var baskets_administration_component_1 = require("./baskets-administration.component");
var baskets_order_administration_component_1 = require("./baskets-order-administration.component");
var basket_administration_component_1 = require("./basket-administration.component");
var entities_administration_component_1 = require("./entities-administration.component");
var entity_administration_component_1 = require("./entity-administration.component");
var doctypes_administration_component_1 = require("./doctypes-administration.component");
var statuses_administration_component_1 = require("./statuses-administration.component");
var status_administration_component_1 = require("./status-administration.component");
var actions_administration_component_1 = require("./actions-administration.component");
var action_administration_component_1 = require("./action-administration.component");
var parameters_administration_component_1 = require("./parameters-administration.component");
var parameter_administration_component_1 = require("./parameter-administration.component");
var priorities_administration_component_1 = require("./priorities-administration.component");
var priority_administration_component_1 = require("./priority-administration.component");
var reports_administration_component_1 = require("./reports-administration.component");
var history_administration_component_1 = require("./history-administration.component");
var historyBatch_administration_component_1 = require("./historyBatch-administration.component");
var update_status_administration_component_1 = require("./update-status-administration.component");
var notifications_administration_component_1 = require("./notifications-administration.component");
var notifications_schedule_administration_component_1 = require("./notifications-schedule-administration.component");
var notification_administration_component_1 = require("./notification-administration.component");
var AdministrationModule = /** @class */ (function () {
    function AdministrationModule() {
    }
    AdministrationModule = __decorate([
        core_1.NgModule({
            imports: [
                common_1.CommonModule,
                forms_1.FormsModule,
                forms_1.ReactiveFormsModule,
                http_1.HttpClientModule,
                app_material_module_1.AppMaterialModule,
                administration_routing_module_1.AdministrationRoutingModule
            ],
            declarations: [
                administration_component_1.AdministrationComponent,
                users_administration_component_1.UsersAdministrationComponent,
                user_administration_component_1.UserAdministrationComponent,
                groups_administration_component_1.GroupsAdministrationComponent,
                group_administration_component_1.GroupAdministrationComponent,
                baskets_administration_component_1.BasketsAdministrationComponent,
                baskets_order_administration_component_1.BasketsOrderAdministrationComponent,
                basket_administration_component_1.BasketAdministrationComponent,
                doctypes_administration_component_1.DoctypesAdministrationComponent,
                entities_administration_component_1.EntitiesAdministrationComponent,
                entity_administration_component_1.EntityAdministrationComponent,
                statuses_administration_component_1.StatusesAdministrationComponent,
                status_administration_component_1.StatusAdministrationComponent,
                actions_administration_component_1.ActionsAdministrationComponent,
                action_administration_component_1.ActionAdministrationComponent,
                parameters_administration_component_1.ParametersAdministrationComponent,
                parameter_administration_component_1.ParameterAdministrationComponent,
                priorities_administration_component_1.PrioritiesAdministrationComponent,
                priority_administration_component_1.PriorityAdministrationComponent,
                reports_administration_component_1.ReportsAdministrationComponent,
                history_administration_component_1.HistoryAdministrationComponent,
                historyBatch_administration_component_1.HistoryBatchAdministrationComponent,
                update_status_administration_component_1.UpdateStatusAdministrationComponent,
                notifications_administration_component_1.NotificationsAdministrationComponent,
                notifications_schedule_administration_component_1.NotificationsScheduleAdministrationComponent,
                notification_administration_component_1.NotificationAdministrationComponent,
                users_administration_component_1.UsersAdministrationRedirectModalComponent,
                entities_administration_component_1.EntitiesAdministrationRedirectModalComponent,
                groups_administration_component_1.GroupsAdministrationRedirectModalComponent,
                basket_administration_component_1.BasketAdministrationSettingsModalComponent,
                basket_administration_component_1.BasketAdministrationGroupListModalComponent
            ],
            entryComponents: [
                users_administration_component_1.UsersAdministrationRedirectModalComponent,
                entities_administration_component_1.EntitiesAdministrationRedirectModalComponent,
                groups_administration_component_1.GroupsAdministrationRedirectModalComponent,
                basket_administration_component_1.BasketAdministrationSettingsModalComponent,
                basket_administration_component_1.BasketAdministrationGroupListModalComponent
            ],
        })
    ], AdministrationModule);
    return AdministrationModule;
}());
exports.AdministrationModule = AdministrationModule;
