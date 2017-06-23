import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { RouterModule }     from '@angular/router';
import { HttpModule }       from '@angular/http';
import { FormsModule }      from '@angular/forms';

import { AppComponent }     from './app.component';
import { ProfileComponent } from './profile.component';
import { ParameterComponent } from './parameter.component';
import { ParametersComponent } from './parameters.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';
import { ActionComponent } from './action.component';
import {ActionsComponent} from "./actions.component";
import {AdministrationComponent} from "./administration.component";
import {UsersAdministrationComponent} from "./users-administration.component";

@NgModule({
  imports:      [
      BrowserModule,
      //DataTablesModule,
      FormsModule,
      RouterModule.forRoot([
          { path: 'administration', component: AdministrationComponent },
          { path: 'administration/users', component: UsersAdministrationComponent },
          { path: 'profile', component: ProfileComponent },
          { path: 'administration/parameter/create', component: ParameterComponent },
          { path: 'administration/parameter/update/:id', component: ParameterComponent },
          { path: 'administration/parameters', component: ParametersComponent },
          { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
          { path: 'administration/actions', component: ActionsComponent },
          { path: 'administration/actions/create', component: ActionComponent },
          { path: 'administration/actions/:id', component: ActionComponent },
          { path: '**',   redirectTo: '', pathMatch: 'full' },
      ], { useHash: true }),
      HttpModule
  ],
  declarations: [ AdministrationComponent,UsersAdministrationComponent,AppComponent,ActionsComponent,ActionComponent, ProfileComponent, ParametersComponent, ParameterComponent, SignatureBookComponent, SafeUrlPipe ],
  providers: [],
  bootstrap:    [ AppComponent]
})
export class AppModule { }
