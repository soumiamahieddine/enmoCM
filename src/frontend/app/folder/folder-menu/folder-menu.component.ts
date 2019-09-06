import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { LANG } from '../../translate.component';
import { HttpClient } from '@angular/common/http';
import { map, tap, catchError, filter, exhaustMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';
import { MatDialogRef, MatDialog } from '@angular/material/dialog';

@Component({
    selector: 'folder-menu',
    templateUrl: "folder-menu.component.html",
    styleUrls: ['folder-menu.component.scss'],
    providers: [NotificationService],
})
export class FolderMenuComponent implements OnInit {

    lang: any = LANG;

    foldersList: any[] = [];
    @Input('resIds') resIds: number[];
    @Input('currentFolders') currentFoldersList: any[];

    @Output('refreshFolders') refreshFolders = new EventEmitter<string>();
    @Output('refreshList') refreshList = new EventEmitter<string>();

    dialogRef: MatDialogRef<any>;
    
    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog
    ) { }

    ngOnInit(): void { }

    getFolders() {
        this.http.get("../../rest/folders").pipe(
            map((data: any) => data.folders),
            tap((data: any) => {
                this.foldersList = data;
            }),
        ).subscribe();
    }

    classifyDocuments(folder: any) {

        this.http.post('../../rest/folders/' + folder.id + '/resources', { resources: this.resIds }).pipe(
            tap(() => {
                this.refreshFolders.emit();
                this.refreshList.emit();
                this.notify.success(this.lang.mailClassified);
            }),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    unclassifyDocuments(folder: any) {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete, msg: 'Voulez-vous enlever <b>' + this.resIds.length + '</b> document(s) du classement ?' } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.request('DELETE', '../../rest/folders/' + folder.id + '/resources', { body: { resources: this.resIds } })),
            tap((data: any) => {
                this.notify.success(this.lang.removedFromFolder);
                this.refreshFolders.emit();
                this.refreshList.emit();
            })
        ).subscribe();
    }
}
