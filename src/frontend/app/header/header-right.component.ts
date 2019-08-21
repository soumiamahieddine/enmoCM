import { Component, OnInit, ViewChild }    from '@angular/core';
import { HttpClient }           from '@angular/common/http';
import { LANG }                 from '../translate.component';
import { HeaderService }        from '../../service/header.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { MatInput } from '@angular/material/input';
import { IndexingGroupModalComponent } from '../menu/menu-shortcut.component';
import { Router } from '@angular/router';
import { AppService } from '../../service/app.service';

declare function $j(selector: any): any;

@Component({
    selector: 'header-right',
    styleUrls: ['header-right.component.scss'],
    templateUrl : "header-right.component.html",
})
export class HeaderRightComponent implements OnInit {

    lang        : any       = LANG;

    dialogRef   : MatDialogRef<any>;
    config      : any       = {};

    hideSearch : boolean = true;

    @ViewChild('searchInput', { static: true }) searchInput: MatInput;

    constructor(
        public http: HttpClient, 
        private router: Router,
        public headerService: HeaderService, 
        public dialog: MatDialog,
        public appService: AppService) {}

    ngOnInit(): void {}

    gotToMenu(shortcut:any) {
        if (shortcut.id == 'indexing' && shortcut.groups.length > 1) {
            this.config = { data: { indexingGroups: shortcut.groups, link:shortcut.servicepage } };
            this.dialogRef = this.dialog.open(IndexingGroupModalComponent, this.config);
        } else if (shortcut.angular == 'true') {
            this.router.navigate([shortcut.servicepage]);
        } else {
            location.href = shortcut.servicepage;
        }
    }

    showSearchInput() {
        this.hideSearch = !this.hideSearch;
        setTimeout(() => {
            this.searchInput.focus(); 
        }, 200);
    }
}