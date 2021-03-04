import { Injectable, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { NgSelectModule } from '@ng-select/ng-select';
import AppComponent from './app.component';
import { AppRoutingModule } from './app-routing.module';


import { VersementComponent } from '../app/versement/versement.component';
import { DropdownComponent } from '../app/dropdown/dropdown.component';

import { HeaderComponent } from '../app/header/header.component';
import { FooterComponent } from '../app/footer/footer.component';
import { TestComponent } from '../app/test/test.component';

@Injectable()

@NgModule({
  declarations: [
    AppComponent,
    TestComponent,
    VersementComponent,
    DropdownComponent,
    HeaderComponent,
    FooterComponent,
  ],
  imports: [
    HttpClientModule,
    BrowserModule,
    FormsModule,
    AppRoutingModule,
    ReactiveFormsModule,
    NgSelectModule,
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
