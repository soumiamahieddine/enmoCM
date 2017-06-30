import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { RouterModule }     from '@angular/router';
import { HttpModule }       from '@angular/http';
import { FormsModule }      from '@angular/forms';


import { AppComponent }                         from './app.component';
import { HeaderComponent }                      from './header.component';
import { UsersAdministrationComponent }         from './users-administration.component';
import { UserAdministrationComponent }          from './user-administration.component';
import { StatusListAdministrationComponent }    from './status-list-administration.component';
import { StatusAdministrationComponent }        from './status-administration.component';
import { ProfileComponent }                     from './profile.component';
import { ParameterComponent }                   from './parameter.component';
import { ParametersComponent }                  from './parameters.component';
import { PrioritiesComponent }                  from './priorities.component';
import { PriorityComponent }                    from './priority.component';

import { AdministrationComponent }              from './administration.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';
import { ReportsComponent } from './reports.component';


import { ActionComponent } from './action.component';
import {ActionsComponent} from "./actions.component";


@NgModule({
  imports:      [
      BrowserModule,
      //DataTablesModule,
      FormsModule,
      RouterModule.forRoot([
          { path: 'administration', component: AdministrationComponent },
          { path: 'administration/users', component: UsersAdministrationComponent },
          { path: 'administration/users/:userId', component: UserAdministrationComponent },
          { path: 'administration/status/create', component: StatusAdministrationComponent },
          { path: 'administration/status/update/:id', component: StatusAdministrationComponent },
          { path: 'administration/status', component: StatusListAdministrationComponent },
          { path: 'profile', component: ProfileComponent },
          { path: 'administration/parameter/create', component: ParameterComponent },
          { path: 'administration/parameter/update/:id', component: ParameterComponent },
          { path: 'administration/parameters', component: ParametersComponent },
          { path: 'administration/reports', component : ReportsComponent},
          { path: 'administration/priorities', component : PrioritiesComponent },
          { path: 'administration/priority/update/:id', component : PriorityComponent },
          { path: 'administration/priority/create', component : PriorityComponent },
          { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
          { path: 'administration/actions', component: ActionsComponent },
          { path: 'administration/actions/create', component: ActionComponent },
          { path: 'administration/actions/:id', component: ActionComponent },
          { path: '**',   redirectTo: '', pathMatch: 'full' },
      ], { useHash: true }),
      HttpModule
  ],
  declarations: [
      HeaderComponent,
      AppComponent,
      ActionsComponent,
      AdministrationComponent,
      ReportsComponent,
      UsersAdministrationComponent,
      UserAdministrationComponent,
      StatusAdministrationComponent,
      StatusListAdministrationComponent,
      PrioritiesComponent,
      PriorityComponent,
      ParametersComponent,
      ParameterComponent,
      ProfileComponent,
      SignatureBookComponent,
      SafeUrlPipe
  ],
  bootstrap:    [ AppComponent]
})
export class AppModule { }
