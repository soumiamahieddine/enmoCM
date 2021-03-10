import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { AppMaterialModule } from './app-material.module';
import { CustomSnackbarComponent } from './service/notification/notification.service';
import AppComponent from './app.component';
import { AppRoutingModule } from './app-routing.module';

import { MessageBoxComponent } from './plugins/messageBox/message-box.component';
import { PanelComponent } from './panel/panel.component';

@NgModule({
  declarations: [
    AppComponent,
    MessageBoxComponent,
    CustomSnackbarComponent,
    PanelComponent,
  ],
  imports: [
    HttpClientModule,
    BrowserModule,
    BrowserAnimationsModule,
    FormsModule,
    AppRoutingModule,
    ReactiveFormsModule,
    AppMaterialModule,
  ],
  entryComponents: [
    CustomSnackbarComponent,
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
