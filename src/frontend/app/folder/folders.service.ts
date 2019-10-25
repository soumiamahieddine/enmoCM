import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../app/translate.component';
import { Subject, Observable, of } from 'rxjs';
import { NotificationService } from '../notification.service';
import { MatDialog } from '@angular/material';
import { Router } from '@angular/router';
import { map, tap, filter, exhaustMap, catchError, finalize } from 'rxjs/operators';
import { ConfirmComponent } from '../../plugins/modal/confirm.component';


@Injectable()
export class FoldersService {

    lang: any = LANG;

    loading: boolean = false;

    pinnedFolders: any = [];

    currentFolder: any = { id: 0 };

    private eventAction = new Subject<any>();

    constructor(
        public http: HttpClient,
        public dialog: MatDialog,
        private notify: NotificationService,
        private router: Router
    ) {
    }

    ngOnInit(): void { }

    initFolder() {
        this.currentFolder = { id: 0 };
    }

    catchEvent(): Observable<any> {
        return this.eventAction.asObservable();
    }

    goToFolder(folder: any) {
        this.setFolder(folder);
        this.router.navigate(['/folders/' + folder.id]);
    }

    setFolder(folder: any) {
        this.currentFolder = folder;
        this.eventAction.next(folder);
    }

    getCurrentFolder() {
        return this.currentFolder;
    }

    getPinnedFolders() {
        this.http.get("../../rest/pinnedFolders").pipe(
            map((data: any) => {
                data.folders = data.folders.map((folder: any) => {
                    return {
                        ...folder,
                        showAction: false
                    }
                });
                return data;
            }),
            tap((data: any) => {
                this.pinnedFolders = data.folders;
            }),
        ).subscribe();
    }

    getPinnedList() {
        return this.pinnedFolders;
    }

    pinFolder(folder: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.pinFolder, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.post(`../../rest/folders/${folder.id}/pin`, {})),
            tap(() => {
                this.getPinnedFolders();
                this.notify.success(this.lang.folderPinned);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    unpinFolder(folder: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.unpinFolder, msg: this.lang.confirmAction } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.delete(`../../rest/folders/${folder.id}/unpin`)),
            tap(() => {
                this.getPinnedFolders();
                this.notify.success(this.lang.folderUnpinned);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getDragIds() {
        return this.pinnedFolders.map((folder: any) => 'folder-list-' + folder.id);
    }

    classifyDocument(ev: any, folder: any) {
        const dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.classify + ' ' + ev.item.data.alt_identifier, msg: this.lang.classifyQuestion + ' <b>' + ev.item.data.alt_identifier + '</b> ' + this.lang.in + ' <b>' + folder.label + '</b>&nbsp;?' } });

        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.post(`../../rest/folders/${folder.id}/resources`, { resources: [ev.item.data.res_id] })),
            tap((data: any) => {
                folder.countResources = data.countResources;
            }),
            tap(() => {
                this.notify.success(this.lang.mailClassified);
                this.eventAction.next({type:'function', content: 'refreshDao'});
            }),
            finalize(() => folder.drag = false),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
