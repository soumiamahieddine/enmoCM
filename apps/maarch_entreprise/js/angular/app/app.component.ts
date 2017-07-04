import { Component } from '@angular/core';

@Component({
  selector: 'my-app',
  template: `<div id="resultInfo" class="fade" style="display:none;"></div><router-outlet></router-outlet>`,
  styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class AppComponent  {}
