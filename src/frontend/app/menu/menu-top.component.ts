import { Component, OnInit, Input  } from '@angular/core';
import { LANG } from '../translate.component';

declare function $j(selector: any) : any;
declare var angularGlobals : any;


@Component({
    selector    : "menu-top",
    templateUrl : "menu-top.component.html",
})
export class MenuTopComponent implements OnInit {

    coreUrl    : string;
    lang       : any       = LANG;
    user       : any       = {};
    mobileMode : boolean   = false;
    @Input() homeData: any;

    constructor() {
        this.mobileMode = angularGlobals.mobileMode;
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
        ]
    }
}
