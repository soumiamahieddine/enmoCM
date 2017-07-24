import { NgModule }              from '@angular/core';
import { RouterModule, Routes }  from '@angular/router';

import { AdministrationComponent }              from './administration.component';
import { UsersAdministrationComponent }         from './users-administration.component';
import { UserAdministrationComponent }          from './user-administration.component';
import { StatusesAdministrationComponent }      from './statuses-administration.component';
import { StatusAdministrationComponent }        from './status-administration.component';
import { ActionsAdministrationComponent }       from './actions-administration.component';
import { ActionAdministrationComponent }        from './action-administration.component';
import { ParameterAdministrationComponent }     from './parameter-administration.component';
import { ParametersAdministrationComponent }    from './parameters-administration.component';
import { PrioritiesAdministrationComponent }    from './priorities-administration.component';
import { PriorityAdministrationComponent }      from './priority-administration.component';
import { ReportsAdministrationComponent }       from './reports-administration.component';


@NgModule({
    imports: [
        RouterModule.forChild([
            { path: 'administration', component: AdministrationComponent },
            { path: 'administration/users', component: UsersAdministrationComponent },
            { path: 'administration/users/new', component: UserAdministrationComponent },
            { path: 'administration/users/:id', component: UserAdministrationComponent },
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
        ]),
    ],
    exports: [
        RouterModule
    ]
})
export class AdministrationRoutingModule {}
