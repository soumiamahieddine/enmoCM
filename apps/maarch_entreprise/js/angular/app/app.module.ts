import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { RouterModule }     from '@angular/router';
import { HttpModule }       from '@angular/http';
import { FormsModule }      from '@angular/forms';

import { AppComponent }                         from './app.component';
//import { HeaderComponent }                      from './header.component';
import { AdministrationComponent }              from './administration.component';
import { UsersAdministrationComponent }         from './users-administration.component';
import { UserAdministrationComponent }          from './user-administration.component';
import { StatusListAdministrationComponent }    from './status-list-administration.component';
import { StatusAdministrationComponent }        from './status-administration.component';
import { ProfileComponent }                     from './profile.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';

@NgModule({
  imports:      [
      BrowserModule,
      FormsModule,
      RouterModule.forRoot([
          { path: 'administration', component: AdministrationComponent },
          { path: 'administration/users', component: UsersAdministrationComponent },
          { path: 'administration/users/new', component: UserAdministrationComponent },
          { path: 'administration/users/:id', component: UserAdministrationComponent },
          { path: 'administration/status/create', component: StatusAdministrationComponent },
          { path: 'administration/status/update/:id', component: StatusAdministrationComponent },
          { path: 'administration/status', component: StatusListAdministrationComponent },
          { path: 'profile', component: ProfileComponent },
          { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
          { path: '**',   redirectTo: '', pathMatch: 'full' },
      ], { useHash: true }),
      HttpModule
  ],
  declarations: [
      AppComponent,
      AdministrationComponent,
      UsersAdministrationComponent,
      UserAdministrationComponent,
      StatusAdministrationComponent,
      StatusListAdministrationComponent,
      ProfileComponent,
      SignatureBookComponent,
      SafeUrlPipe
  ],
  bootstrap:    [ AppComponent]
})
export class AppModule { }