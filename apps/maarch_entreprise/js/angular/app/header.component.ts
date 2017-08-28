import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';

declare function $j(selector: any) : any;

declare var angularGlobals : any;


@Component({
    selector: 'menu-app',
    templateUrl :   angularGlobals["headerView"],
    styleUrls   :   [
        'css/header.component.css', //load specific custom css
    ]
})
export class HeaderComponent implements OnInit {

    coreUrl                     : string;

    applicationName             : string    = "";
    adminList                   : any[]     = [];
    adminListModule             : any[]     = [];
    menuList                    : any[]     = [];
    profilList                  : any[]     = [];
    notifList                   : any[]     = [];


    constructor(public http: HttpClient) {
    }

    prepareHeader() {
        $j('#maarch_content').remove();
    }

    ngOnInit(): void {
        this.prepareHeader();
        
        this.coreUrl = angularGlobals.coreUrl;

        this.http.get(this.coreUrl + 'rest/administration')
        .subscribe((data : any) => {
            this.menuList = data.menu.menuList;
            this.applicationName = data.menu.applicationName[0];
            this.adminList = data.application;
            this.adminListModule = data.modules;
        });

        this.profilList = [
            {   
                label   : 'Mon profil',
                link    : '/profile',
                style    : 'fa-user'
            },
            {   label   : 'Déconnexion',
                link    : '/logout',
                style    : 'fa-sign-out'
            }
        ]
    }
}
