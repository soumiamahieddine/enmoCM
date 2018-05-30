import { CommonModule }                         from '@angular/common';

import { NgModule }                             from '@angular/core';

/*CORE IMPORTS*/
import { BrowserModule }                        from '@angular/platform-browser';
import { BrowserAnimationsModule }              from '@angular/platform-browser/animations';
import { FormsModule, ReactiveFormsModule }     from '@angular/forms';
import { HttpClientModule }                     from '@angular/common/http';
import { RouterModule }                         from '@angular/router';

/*FRONT IMPORTS*/
import { AppMaterialModule }                    from './app-material.module';

/*MENU COMPONENT*/
import { MenuComponent }                        from './menu/menu.component';
import { MenuNavComponent }                     from './menu/menu-nav.component';
import { MenuTopComponent }                     from './menu/menu-top.component';


@NgModule({
    imports: [
        CommonModule,
        BrowserModule,
        BrowserAnimationsModule,
        FormsModule,
        HttpClientModule,
        RouterModule,
        AppMaterialModule,
    ],
    declarations: [
        MenuComponent,
        MenuNavComponent,
        MenuTopComponent
    ],
    exports: [
        CommonModule,
        MenuComponent,
        MenuNavComponent,
        MenuTopComponent,
        BrowserModule,
        BrowserAnimationsModule,
        FormsModule,
        ReactiveFormsModule,
        HttpClientModule,
        RouterModule,
        AppMaterialModule
    ]
})
export class SharedModule { }
