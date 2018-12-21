import { Component, AfterViewInit, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MAT_BOTTOM_SHEET_DATA } from '@angular/material';

declare function $j(selector: any): any;

@Component({
    selector: 'app-notes-list',
    templateUrl: 'notes-list.component.html',
    styleUrls: ['notes-list.component.scss'],
    providers: [NotificationService]
})
export class NotesListComponent implements AfterViewInit {

    lang: any = LANG;
    notes: any;
    loading: boolean = true;

    constructor(public http: HttpClient, @Inject(MAT_BOTTOM_SHEET_DATA) public data: any) { }

    ngAfterViewInit() {
        this.http.get("../../rest/res/" + this.data.resId + "/notes")
            .subscribe((data: any) => {
                this.notes = data;
                this.loading = false;
            });
    }
}