import { Component, AfterViewInit, Input, EventEmitter, Output } from '@angular/core';
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
    loading: boolean = false;
    templatesNote: any = [];

    @Input('title') title: string = this.lang.addNote;
    @Input('content') content: string = '';
    @Input('resIds') resIds: any[];
    @Input('addMode') addMode: boolean;
    @Input('upMode') upMode: boolean;
    @Input('noteContent') noteContent: string;
    @Input('noteId') noteId: number;
    @Output('refreshNotes') refreshNotes = new EventEmitter<string>();

    constructor(public http: HttpClient) { }

    ngOnInit() {
        if (this.upMode) {
            this.content = this.noteContent;
        }
    }

    ngAfterViewInit() {
    }

    addNote() {
        this.loading = true;
        this.http.post("../../rest/notes", { value: this.content, resId: this.resIds[0] })
            .subscribe((data: any) => {
                this.refreshNotes.emit(this.resIds[0]);
                this.loading = false;
            });
    }

    updateNote() {
        this.loading = true;
        this.http.put("../../rest/notes/" + this.noteId, { value: this.content, resId: this.resIds[0] })
            .subscribe((data: any) => {
                this.refreshNotes.emit(this.resIds[0]);
                this.loading = false;
            });
    }

    getNoteContent() {
        return this.content;
    }

    selectTemplate(template: any) {
        if (this.content.length > 0) {
            this.content = this.content + ' ' + template.template_content;
        } else {
            this.content = template.template_content;
        }

    }

    getTemplatesNote() {
        if (this.templatesNote.length == 0) {
            let params = {};
            if (this.resIds.length == 1) {
                params['resId'] = this.resIds[0];
            }
            this.http.get("../../rest/notesTemplates", { params: params })
                .subscribe((data: any) => {
                    this.templatesNote = data['templates'];
                });

        }
    }
}
