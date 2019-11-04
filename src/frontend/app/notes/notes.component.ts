import { Component, Input, OnInit, EventEmitter, Output } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

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
    resIds : number[] = [];

    @Input('injectDatas') injectDatas: any;

    @Input('resId') resId: number = null;
    @Input('editMode') editMode: boolean = false;

    @Output('reloadBadgeNotes') reloadBadgeNotes = new EventEmitter<string>();

    constructor(
        public http: HttpClient,
        private notify: NotificationService
    ) { }

    ngOnInit(): void {
        if (this.resId !== null) {
            this.http.get(`../../rest/resources/${this.resId}/notes`).pipe(
                tap((data: any) => {
                    this.resIds[0] = this.resId;
                    this.notes = data['notes'];
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    loadNotes(resId: number) {
        this.resIds[0] = resId;
        this.loading = true;
        this.http.get("../../rest/resources/" + this.resIds[0] + "/notes")
        .subscribe((data: any) => {
            this.notes = data['notes'];
            this.reloadBadgeNotes.emit(`${this.notes.length}`);
            this.loading = false;
        });
    }
}
