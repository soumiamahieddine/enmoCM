import { Component, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

@Component({
    selector: 'app-note-editor',
    templateUrl: 'note-editor.component.html',
    styleUrls: ['note-editor.component.scss'],
    providers: [NotificationService]
})
export class NoteEditorComponent implements AfterViewInit {

    lang: any = LANG;
    notes: any;
    loading: boolean = true;

    content: string = '';

    @Input('mode') mode: any;

    constructor(public http: HttpClient) { }

    ngAfterViewInit() { }

    addNote() {
        /*this.http.get("../../rest/res/" + this.data.resId + "/notes")
        .subscribe((data: any) => {
            this.notes = data;
            this.loading = false;
        });*/
    }


    getNoteContent() {
        return this.content;
    }
}