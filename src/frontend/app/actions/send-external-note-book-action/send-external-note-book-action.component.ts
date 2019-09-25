import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { map, tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    templateUrl: "send-external-note-book-action.component.html",
    styleUrls: ['send-external-note-book-action.component.scss'],
    providers: [NotificationService],
})
export class SendExternalNoteBookActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;
    additionalsInfos: any = {
        users: [],
        mails: [],
        noMail: []
    };

    externalSignatoryBookDatas: any = {
        processingUser: ''
    };
    errors: any;

    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<SendExternalNoteBookActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        this.loading = true;

        this.http.post('../../rest/resourcesList/users/' + this.data.currentBasketInfo.ownerId + '/groups/' + this.data.currentBasketInfo.groupId + '/baskets/' + this.data.currentBasketInfo.basketId + '/checkExternalNoteBook', { resources: this.data.selectedRes }).pipe(
            map((data: any) => {
                data.additionalsInfos.users.forEach((element: any) => {
                    element.displayName = element.firstname + ' ' + element.lastname;
                });
                return data;
            }),
            tap((data) => {
                this.additionalsInfos = data.additionalsInfos;
                this.errors = data.errors;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    onSubmit(): void {
        this.loading = true;

        let realResSelected: string[];
        let datas: any;

        realResSelected = this.additionalsInfos.mails.map((e: any) => { return e.res_id; });
        datas = this.externalSignatoryBookDatas;

        this.http.put('../../rest/resourcesList/users/' + this.data.currentBasketInfo.ownerId + '/groups/' + this.data.currentBasketInfo.groupId + '/baskets/' + this.data.currentBasketInfo.basketId + '/actions/' + this.data.action.id, { resources: realResSelected, note: this.noteEditor.getNoteContent(), data: datas })
            .subscribe((data: any) => {
                if (!data) {
                    this.dialogRef.close('success');
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
                this.loading = false;
            }, (err: any) => {
                this.notify.handleErrors(err);
                this.loading = false;
            });
    }

    checkValidAction() {
        if (this.additionalsInfos.mails.length == 0 || !this.externalSignatoryBookDatas.processingUser || this.additionalsInfos.users.length == 0) {
            return true;
        } else {
            return false;
        }
    }

    setVal(user: any) {
        this.externalSignatoryBookDatas.processingUser = user.id;
    }
}
