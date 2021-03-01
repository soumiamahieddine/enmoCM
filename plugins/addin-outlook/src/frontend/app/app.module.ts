import { Injectable, NgModule } from "@angular/core";
import { BrowserModule } from "@angular/platform-browser";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { NgSelectModule } from '@ng-select/ng-select';
import AppComponent from "./app.component";

import { VersementComponent } from "../app/versement/versement.component";
import { DropdownComponent } from "../app/dropdown/dropdown.component";

import { HeaderComponent } from "../app/header/header.component";
import { FooterComponent } from "../app/footer/footer.component";

@Injectable()

@NgModule({
  declarations: [
    AppComponent,
    VersementComponent,
    DropdownComponent,
    HeaderComponent,
    FooterComponent,
  ],
  imports: [
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
    NgSelectModule,
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
