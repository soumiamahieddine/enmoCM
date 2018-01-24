import { NgModule }              from '@angular/core';
import { RouterModule }  from '@angular/router';

import { AdministrationComponent }              from './administration.component';
import { UsersAdministrationComponent }         from './users-administration.component';
import { UserAdministrationComponent }          from './user-administration.component';
import { GroupsAdministrationComponent }        from './groups-administration.component';
import { GroupAdministrationComponent }         from './group-administration.component';
import { BasketsAdministrationComponent }       from './baskets-administration.component';
import { BasketsOrderAdministrationComponent }  from './baskets-order-administration.component';
import { BasketAdministrationComponent }        from './basket-administration.component';
import { StatusesAdministrationComponent }      from './statuses-administration.component';
import { StatusAdministrationComponent }        from './status-administration.component';
import { ActionsAdministrationComponent }       from './actions-administration.component';
import { ActionAdministrationComponent }        from './action-administration.component';
import { ParameterAdministrationComponent }     from './parameter-administration.component';
import { ParametersAdministrationComponent }    from './parameters-administration.component';
import { PrioritiesAdministrationComponent }    from './priorities-administration.component';
import { PriorityAdministrationComponent }      from './priority-administration.component';
import { ReportsAdministrationComponent }       from './reports-administration.component';
import { NotificationsAdministrationComponent } from './notifications-administration.component';
import { NotificationAdministrationComponent }  from './notification-administration.component';
import { HistoryAdministrationComponent }       from './history-administration.component';
import { HistoryBatchAdministrationComponent }  from './historyBatch-administration.component';
import { UpdateStatusAdministrationComponent }  from './update-status-administration.component';


@NgModule({
    imports: [
        RouterModule.forChild([
            { path: 'administration', component: AdministrationComponent },
            { path: 'administration/users', component: UsersAdministrationComponent },
            { path: 'administration/users/new', component: UserAdministrationComponent },
            { path: 'administration/users/:id', component: UserAdministrationComponent },
            { path: 'administration/groups', component: GroupsAdministrationComponent },
            { path: 'administration/groups/new', component: GroupAdministrationComponent },
            { path: 'administration/groups/:id', component: GroupAdministrationComponent },
            { path: 'administration/baskets', component: BasketsAdministrationComponent },
            { path: 'administration/baskets-sorted', component: BasketsOrderAdministrationComponent },
            { path: 'administration/baskets/new', component: BasketAdministrationComponent },
            { path: 'administration/baskets/:id', component: BasketAdministrationComponent },
            { path: 'administration/status', component: StatusesAdministrationComponent },
            { path: 'administration/status/new', component: StatusAdministrationComponent },
            { path: 'administration/status/:identifier', component: StatusAdministrationComponent },
            { path: 'administration/parameters', component: ParametersAdministrationComponent },
            { path: 'administration/parameters/new', component: ParameterAdministrationComponent },
            { path: 'administration/parameters/:id', component: ParameterAdministrationComponent },
            { path: 'administration/reports', component : ReportsAdministrationComponent},
            { path: 'administration/priorities', component : PrioritiesAdministrationComponent },
            { path: 'administration/priorities/new', component : PriorityAdministrationComponent },
            { path: 'administration/priorities/:id', component : PriorityAdministrationComponent },
            { path: 'administration/actions', component: ActionsAdministrationComponent },
            { path: 'administration/actions/new', component: ActionAdministrationComponent },
            { path: 'administration/actions/:id', component: ActionAdministrationComponent },
            { path: 'administration/notifications', component: NotificationsAdministrationComponent },
            { path: 'administration/notifications/new', component: NotificationAdministrationComponent },
            { path: 'administration/notifications/:identifier', component: NotificationAdministrationComponent },
            { path: 'administration/history', component: HistoryAdministrationComponent },
            { path: 'administration/historyBatch', component: HistoryBatchAdministrationComponent },
            { path: 'administration/update-status', component: UpdateStatusAdministrationComponent },
        ]),
    ],
    exports: [
        RouterModule
    ]
})
export class AdministrationRoutingModule {}
