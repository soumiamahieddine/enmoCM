import { Injectable, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { AppMaterialModule } from './app-material.module';
import AppComponent from './app.component';
import { AppRoutingModule } from './app-routing.module';

import { TestComponent } from '../app/test/test.component';

@Injectable()

@NgModule({
  declarations: [
    AppComponent,
    TestComponent,
  ],
  imports: [
    HttpClientModule,
    BrowserModule,
    FormsModule,
    AppRoutingModule,
    ReactiveFormsModule,
    AppMaterialModule,
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
