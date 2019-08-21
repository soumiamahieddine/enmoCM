import { Component, OnInit, Input }    from '@angular/core';
import { LANG }                 from '../translate.component';
import { HeaderService }        from '../../service/header.service';
import { MatDialogRef } from '@angular/material/dialog';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../service/app.service';

@Component({
    selector: 'header-panel',
    styleUrls: ['header-panel.component.scss'],
    templateUrl : "header-panel.component.html",
})
export class HeaderPanelComponent implements OnInit {

    lang        : any       = LANG;

    dialogRef   : MatDialogRef<any>;
    config      : any       = {};

    @Input('snavLeft') snavLeft: MatSidenav;
    
    constructor(
        public headerService: HeaderService,
        public appService: AppService
    ) { }

    ngOnInit(): void { }
}