import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { RouterModule }     from '@angular/router';
import { FormsModule }      from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';

import { AppComponent }                         from './app.component';
import { AdministrationComponent }              from './administration.component';
import { UsersAdministrationComponent }         from './users-administration.component';
import { UserAdministrationComponent }          from './user-administration.component';
import { StatusListAdministrationComponent }    from './status-list-administration.component';
import { StatusAdministrationComponent }        from './status-administration.component';
import { ActionsAdministrationComponent }       from './actions-administration.component';
import { ActionAdministrationComponent }        from './action-administration.component';
import { ParameterAdministrationComponent }     from './parameter-administration.component';
import { ParametersAdministrationComponent }    from './parameters-administration.component';
import { PrioritiesAdministrationComponent }    from './priorities-administration.component';
import { PriorityAdministrationComponent }      from './priority-administration.component';
import { ProfileComponent }                     from './profile.component';

import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';
import { ReportsComponent } from './reports.component';

@NgModule({
  imports:      [
      BrowserModule,
      //DataTablesModule,
      FormsModule,
      RouterModule.forRoot([
          { path: 'administration', component: AdministrationComponent },
          { path: 'administration/users', component: UsersAdministrationComponent },
          { path: 'administration/users/new', component: UserAdministrationComponent },
          { path: 'administration/users/:id', component: UserAdministrationComponent },
          { path: 'administration/status', component: StatusListAdministrationComponent },
          { path: 'administration/status/new', component: StatusAdministrationComponent },
          { path: 'administration/status/:identifier', component: StatusAdministrationComponent },
          { path: 'profile', component: ProfileComponent },
          { path: 'administration/parameters', component: ParametersAdministrationComponent },
          { path: 'administration/parameters/new', component: ParameterAdministrationComponent },
          { path: 'administration/parameters/:id', component: ParameterAdministrationComponent },
          { path: 'administration/reports', component : ReportsComponent},
          { path: 'administration/priorities', component : PrioritiesAdministrationComponent },
          { path: 'administration/priorities/new', component : PriorityAdministrationComponent },
          { path: 'administration/priorities/:id', component : PriorityAdministrationComponent },
          { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
          { path: 'administration/actions', component: ActionsAdministrationComponent },
          { path: 'administration/actions/new', component: ActionAdministrationComponent },
          { path: 'administration/actions/:id', component: ActionAdministrationComponent },
          { path: '**',   redirectTo: '', pathMatch: 'full' },
      ], { useHash: true }),
      HttpClientModule
  ],
  declarations: [
      AppComponent,
      ActionAdministrationComponent,
      ActionsAdministrationComponent,
      AdministrationComponent,
      ReportsComponent,
      UsersAdministrationComponent,
      UserAdministrationComponent,
      StatusAdministrationComponent,
      StatusListAdministrationComponent,
      PrioritiesAdministrationComponent,
      PriorityAdministrationComponent,
      ParametersAdministrationComponent,
      ParameterAdministrationComponent,
      ProfileComponent,
      SignatureBookComponent,
      SafeUrlPipe
  ],
  bootstrap:    [ AppComponent]
})
export class AppModule { }
