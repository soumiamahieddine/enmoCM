import { NgModule }         from '@angular/core';
import { CommonModule }     from '@angular/common';
import { FormsModule }      from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';

import { AppMaterialModule }                    from '../app-material.module';
import { AdministrationRoutingModule }          from './administration-routing.module';

import { AdministrationComponent }              from './administration.component';
import { NotificationsAdministrationComponent }         from './notifications-administration.component';
//import { NotificationAdministrationComponent }          from './notification-administration.component';
import { UsersAdministrationComponent }         from './users-administration.component';
import { UserAdministrationComponent }          from './user-administration.component';
import { StatusesAdministrationComponent }      from './statuses-administration.component';
import { StatusAdministrationComponent }        from './status-administration.component';
import { ActionsAdministrationComponent }       from './actions-administration.component';
import { ActionAdministrationComponent }        from './action-administration.component';
import { ParametersAdministrationComponent }    from './parameters-administration.component';
import { ParameterAdministrationComponent }     from './parameter-administration.component';
import { PrioritiesAdministrationComponent }    from './priorities-administration.component';
import { PriorityAdministrationComponent }      from './priority-administration.component';
import { ReportsAdministrationComponent }       from './reports-administration.component';

@NgModule({
    imports:      [
        CommonModule,
        FormsModule,
        HttpClientModule,
        AppMaterialModule,
        AdministrationRoutingModule
    ],
    declarations: [
        AdministrationComponent,
        NotificationsAdministrationComponent,
        //NotificationAdministrationComponent,
        UsersAdministrationComponent,
        UserAdministrationComponent,
        StatusesAdministrationComponent,
        StatusAdministrationComponent,
        ActionsAdministrationComponent,
        ActionAdministrationComponent,
        ParametersAdministrationComponent,
        ParameterAdministrationComponent,
        PrioritiesAdministrationComponent,
        PriorityAdministrationComponent,
        ReportsAdministrationComponent
    ]
})
export class AdministrationModule { }