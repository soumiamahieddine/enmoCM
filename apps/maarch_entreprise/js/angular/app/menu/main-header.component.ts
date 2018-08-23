import { Component, OnInit, Input, NgZone, ViewChild  } from '@angular/core';
import { LANG } from '../translate.component';
import { MatSidenav } from '@angular/material';

declare function $j(selector: any) : any;
declare var angularGlobals : any;


@Component({
    selector    : "main-header",
    templateUrl : "../../../../Views/main-header.component.html",
})
export class MainHeaderComponent implements OnInit {

    coreUrl    : string;
    lang       : any       = LANG;
    user       : any       = {};
    mobileMode : boolean   = false;
    titleHeader: string;

    snav : MatSidenav;
    snav2 : MatSidenav;

    constructor(private zone: NgZone) {
        this.mobileMode = angularGlobals.mobileMode;
        window['MainHeaderComponent'] = {
            refreshTitle: (value: string) => this.setTitle(value),
            setSnav: (value: MatSidenav) => this.getSnav(value),
            setSnavRight: (value: MatSidenav) => this.getSnavRight(value),
        };
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.user = angularGlobals.user;
        this.user.menu = [
            {"label":"Administration","url":"zefpokezf","icon":"fa-cogs"},
            {"label":"Impression des séparateurs","url":"zefpokezf","icon":"fa-print"},
            {"label":"Enregistrer un courrier","url":"zefpokezf","icon":"fa-plus"},
            {"label":"Rechercher un courrier","url":"zefpokezf","icon":"fa-search"},
            {"label":"Rechercher un contact","url":"zefpokezf","icon":"fa-user"},
            {"label":"Mes contacts","url":"zefpokezf","icon":"fa-book"},
            {"label":"Plan de classement","url":"zefpokezf","icon":"fa-copy"},
            {"label":"Créer un dossier","url":"zefpokezf","icon":"fa-folder-open"},
            {"label":"Consulter un dossier","url":"zefpokezf","icon":"fa-code-branch"},
            {"label":"Rechercher un dossier","url":"zefpokezf","icon":"fa-folder"},
            {"label":"Statistique","url":"zefpokezf","icon":"fa-chart-area"},
            {"label":"Enregistrer un pli numérique","url":"zefpokezf","icon":"fa-file-archive"},
        ];
    }

    setTitle(title: string) {
        this.zone.run(() => this.titleHeader = title);
    }

    getSnav(snav:MatSidenav) {
        this.zone.run(() => this.snav = snav);
    }

    getSnavRight(snav2:MatSidenav) {
        this.zone.run(() => this.snav2 = snav2);
    }
}
