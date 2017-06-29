import { Component } from '@angular/core';

@Component({
  selector: 'my-app',
  template: `<router-outlet></router-outlet>`,
  /*template: `<menu-app></menu-app><router-outlet></router-outlet>`,*/ //for header V2 inclusion IN PROGRESS
})
export class AppComponent  {}
