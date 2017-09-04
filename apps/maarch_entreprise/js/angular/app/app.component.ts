import { Component, ViewEncapsulation } from '@angular/core';

@Component({
    selector: 'my-app',
    //template: `<menu-app></menu-app><router-outlet></router-outlet>`,
    template: `<router-outlet></router-outlet>`,
    encapsulation: ViewEncapsulation.None,
    styleUrls   : [
        '../../node_modules/bootstrap/dist/css/bootstrap.min.css',
        '../../node_modules/@angular/material/prebuilt-themes/indigo-pink.css',
        'css/engine.css',
        'css/jstree-custom.min.css' //treejs module
    ]
})
export class AppComponent  {}
