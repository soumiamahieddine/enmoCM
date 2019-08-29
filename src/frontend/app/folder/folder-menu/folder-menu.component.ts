import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { LANG } from '../../translate.component';
import { HttpClient } from '@angular/common/http';
import { map, tap, catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../notification.service';

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

    @Output('refreshFolders') refreshFolders = new EventEmitter<string>();
    @Output('refreshList') refreshList = new EventEmitter<string>();

    constructor(
        public http: HttpClient,
        private notify: NotificationService
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
                this.notify.success('Courrier classé');
            }),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

}
