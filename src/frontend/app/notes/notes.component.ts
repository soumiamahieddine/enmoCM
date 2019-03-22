import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

@Component({
    selector: 'app-notes-list',
    templateUrl: 'notes-list.component.html',
    styleUrls: ['notes-list.component.scss'],
    providers: [NotificationService]
})
export class NotesListComponent implements OnInit {

    lang: any = LANG;
    notes: any[] = [];
    loading: boolean = false;

    @Input('injectDatas') injectDatas: any;

    constructor(public http: HttpClient) { }

    ngOnInit(): void { }

    loadNotes(resId: number) {
        this.loading = true;
        this.http.get("../../rest/res/" + resId + "/notes")
        .subscribe((data: any) => {
            this.notes = data;
            this.loading = false;
        });
    }
}