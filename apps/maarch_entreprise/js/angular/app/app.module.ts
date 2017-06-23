import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { RouterModule }     from '@angular/router';
import { HttpModule }       from '@angular/http';
import { FormsModule }      from '@angular/forms';


import { AppComponent }                         from './app.component';
import { ProfileComponent }                     from './profile.component';
import { ParameterComponent }                   from './parameter.component';
import { ParametersComponent }                  from './parameters.component';
import { PrioritiesComponent }                  from './priorities.component';
import { PriorityComponent }                    from './priority.component';

import { AdministrationComponent }              from './administration.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';

@NgModule({
  imports:      [
      BrowserModule,
      //DataTablesModule,
      FormsModule,
      RouterModule.forRoot([
          { path: 'administration', component: AdministrationComponent },
          { path: 'profile', component: ProfileComponent },
          { path: 'administration/parameter/create', component: ParameterComponent },
          { path: 'administration/parameter/update/:id', component: ParameterComponent },
          { path: 'administration/parameters', component: ParametersComponent },
          { path: 'administration/priorities', component : PrioritiesComponent },
          { path: 'administration/priority/update/:id', component : PriorityComponent },
          { path: 'administration/priority/create', component : PriorityComponent },
          { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
          { path: '**',   redirectTo: '', pathMatch: 'full' },
      ], { useHash: true }),
      HttpModule
  ],

  declarations: [ AppComponent, AdministrationComponent, PrioritiesComponent,PriorityComponent,ProfileComponent, ParametersComponent, ParameterComponent, SignatureBookComponent, SafeUrlPipe ],
  providers: [],

  bootstrap:    [ AppComponent]
})
export class AppModule { }
