import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { RouterModule }     from '@angular/router';
import { HttpModule }       from '@angular/http';
import { FormsModule }      from '@angular/forms';

import { AppComponent }     from './app.component';
import { ProfileComponent } from './profile.component';
import { ParameterComponent } from './parameter.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';

@NgModule({
  imports:      [
      BrowserModule,
      FormsModule,
      RouterModule.forRoot([
          { path: 'profile', component: ProfileComponent },
          { path: 'parameter/create', component: ParameterComponent },
          { path: 'parameter/update/:id', component: ParameterComponent },
          { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
          { path: '**',   redirectTo: '', pathMatch: 'full' },
      ], { useHash: true }),
      HttpModule
  ],
  declarations: [ AppComponent, ProfileComponent, ParameterComponent, SignatureBookComponent, SafeUrlPipe ],
  bootstrap:    [ AppComponent]
})
export class AppModule { }
