import { NgModule }                             from '@angular/core';
import { BrowserModule }                        from '@angular/platform-browser';
import { BrowserAnimationsModule }              from '@angular/platform-browser/animations';
import { FormsModule }                          from '@angular/forms';
import { HttpClientModule }                     from '@angular/common/http';
import { AppMaterialModule }                    from './app-material.module';
import { Md2Module }                            from 'md2';

import { HeaderComponent }                      from './header.component';
import { AppComponent }                         from './app.component';
import { AppRoutingModule }                     from './app-routing.module';
import { AdministrationModule }                 from './administration/administration.module';

import { ProfileComponent }                     from './profile.component';
import { SignatureBookComponent, SafeUrlPipe }  from './signature-book.component';

@NgModule({
    imports: [
        BrowserModule,
        BrowserAnimationsModule,
        FormsModule,
        HttpClientModule,
        AdministrationModule,
        AppRoutingModule,
        AppMaterialModule,
        Md2Module
    ],
    declarations: [
        HeaderComponent,
        AppComponent,
        ProfileComponent,
        SignatureBookComponent,
        SafeUrlPipe
    ],
    bootstrap: [AppComponent]
})
export class AppModule { }
