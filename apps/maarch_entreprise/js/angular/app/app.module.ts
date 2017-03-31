import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { HttpModule } from '@angular/http';

import { AppComponent }  from './app.component';
import { ProfileComponent }  from './profile.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';

@NgModule({
  imports:      [
      BrowserModule,
      RouterModule.forRoot([
          { path: 'profile', component: ProfileComponent },
          { path: ':basketId/signatureBook/:resId', component: SignatureBookComponent },
          { path: '**',   redirectTo: '', pathMatch: 'full' },
      ], { useHash: true }),
      HttpModule
  ],
  declarations: [ AppComponent, ProfileComponent, SignatureBookComponent, SafeUrlPipe ],
  bootstrap:    [ AppComponent]
})
export class AppModule { }
