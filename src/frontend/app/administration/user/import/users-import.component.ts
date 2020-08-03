import { Component, OnInit, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '../../../../service/notification/notification.service';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialog } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';
import { MatTableDataSource } from '@angular/material/table';
import { FunctionsService } from '../../../../service/functions.service';
import { ConfirmComponent } from '../../../../plugins/modal/confirm.component';
import { filter, exhaustMap, tap, catchError } from 'rxjs/operators';
import { of } from 'rxjs/internal/observable/of';
import { AlertComponent } from '../../../../plugins/modal/alert.component';

@Component({
    templateUrl: 'users-import.component.html',
    styleUrls: ['users-import.component.scss']
})
export class UsersImportComponent implements OnInit {

    loading: boolean = false;
    userColmuns: string[] = [
        'id',
        'user_id',
        'firstname',
        'lastname',
        'mail',
        'phone',
    ];

    csvColumns: string[] = [

    ];

    associatedColmuns: any = {};
    dataSource = new MatTableDataSource(null);
    csvData: any[] = [];
    userData: any[] = [];
    countAll: number = 0;
    countAdd: number = 0;
    countUp: number = 0;

    constructor(
        private translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        private functionsService: FunctionsService,
        public dialog: MatDialog,
        public dialogRef: MatDialogRef<UsersImportComponent>,
        @Inject(MAT_DIALOG_DATA) public data: any,
    ) {
    }

    ngOnInit(): void { }

    changeColumn(coldb: string, colCsv: string) {
        console.log(coldb);
        console.log(colCsv);
        this.userData = [];
        for (let index = 0; index < 10; index++) {
            const data = this.csvData[index];
            this.userData.push({
                'id': coldb === 'id' ? data[this.csvColumns.filter(col => col === colCsv)[0]] : data[this.associatedColmuns['id']],
                'user_id': coldb === 'user_id' ? data[this.csvColumns.filter(col => col === colCsv)[0]] : data[this.associatedColmuns['user_id']],
                'firstname': coldb === 'firstname' ? data[this.csvColumns.filter(col => col === colCsv)[0]] : data[this.associatedColmuns['firstname']],
                'lastname': coldb === 'lastname' ? data[this.csvColumns.filter(col => col === colCsv)[0]] : data[this.associatedColmuns['lastname']],
                'mail': coldb === 'mail' ? data[this.csvColumns.filter(col => col === colCsv)[0]] : data[this.associatedColmuns['mail']],
                'phone': coldb === 'phone' ? data[this.csvColumns.filter(col => col === colCsv)[0]] : data[this.associatedColmuns['phone']]
            });
        }

        this.dataSource = new MatTableDataSource(this.userData);
    }

    uploadCsv(fileInput: any) {
        if (fileInput.target.files && fileInput.target.files[0] && fileInput.target.files[0].type === 'text/csv') {
            this.loading = true;

            let rawCsv = [];
            const reader = new FileReader();

            reader.readAsText(fileInput.target.files[0]);

            reader.onload = (value: any) => {
                console.log(value.target.result.split('\n'));
                rawCsv = value.target.result.split('\n');

                if (rawCsv[0].split(';').map(s => s.replace(/"/gi, '').trim()).length >= this.userColmuns.length) {
                    this.csvColumns = rawCsv[0].split(';').map(s => s.replace(/"/gi, '').trim());
                    let dataCol = [];
                    let objData = {};

                    this.countAll = rawCsv.length - 1;

                    for (let index = 1; index < rawCsv.length; index++) {
                        objData = {};
                        dataCol = rawCsv[index].split(';').map(s => s.replace(/"/gi, '').trim());
                        dataCol.forEach((element: any, index2: number) => {
                            objData[this.csvColumns[index2]] = element;
                        });
                        this.csvData.push(objData);
                    }
                    this.initData();

                    this.countAdd = this.csvData.filter((data: any) => this.functionsService.empty(data[this.associatedColmuns['id']])).length;
                    this.countUp = this.csvData.filter((data: any) => !this.functionsService.empty(data[this.associatedColmuns['id']])).length;
                } else {
                    this.dialog.open(AlertComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: 'Erreur', msg: 'Les données doivent avoir au mimimum <b>6</b> valeurs' } });
                }
                this.loading = false;
            };
        } else {
            this.dialog.open(AlertComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: this.translate.instant('lang.notAllowedExtension') + ' !', msg: this.translate.instant('lang.file') + ' : <b>' + fileInput.target.files[0].name + '</b>, ' + this.translate.instant('lang.type') + ' : <b>' + fileInput.target.files[0].type + '</b><br/><br/><u>' + this.translate.instant('lang.allowedExtensions') + '</u> : <br/>' + 'text/csv' } });
        }
    }

    initData() {
        this.userData = [];
        for (let index = 0; index < 10; index++) {
            const data = this.csvData[index];
            this.associatedColmuns['id'] = this.csvColumns[0];
            this.associatedColmuns['user_id'] = this.csvColumns[1];
            this.associatedColmuns['firstname'] = this.csvColumns[2];
            this.associatedColmuns['lastname'] = this.csvColumns[3];
            this.associatedColmuns['mail'] = this.csvColumns[4];
            this.associatedColmuns['phone'] = this.csvColumns[5];


            this.userData.push({
                'id': data[this.csvColumns[0]],
                'user_id': data[this.csvColumns[1]],
                'firstname': data[this.csvColumns[2]],
                'lastname': data[this.csvColumns[3]],
                'mail': data[this.csvColumns[4]],
                'phone': data[this.csvColumns[5]]
            });
        }
        this.dataSource = new MatTableDataSource(this.userData);
    }

    dndUploadFile(event: any) {
        const fileInput = {
            target: {
                files: [
                    event[0]
                ]
            }
        };
        this.uploadCsv(fileInput);
    }

    onSubmit() {
        console.log('test');
        const dialogRef = this.dialog.open(ConfirmComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: 'Importer', msg: 'Voulez-vous importer <b>' + this.countAll + '</b> utilisateurs ?<br/>(<b>' + this.countAdd + '</b> créations et <b>' + this.countUp + '</b> modifications)' } });
        dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            tap(() => {
                this.loading = true;
                const dataToSend: any[] = [];
                this.csvData.forEach(element => {
                    this.associatedColmuns['id'] = this.csvColumns[0];
                    this.associatedColmuns['user_id'] = this.csvColumns[1];
                    this.associatedColmuns['firstname'] = this.csvColumns[2];
                    this.associatedColmuns['lastname'] = this.csvColumns[3];
                    this.associatedColmuns['mail'] = this.csvColumns[4];
                    this.associatedColmuns['phone'] = this.csvColumns[5];

                    dataToSend.push({
                        'id': element[this.csvColumns[0]],
                        'user_id': element[this.csvColumns[1]],
                        'firstname': element[this.csvColumns[2]],
                        'lastname': element[this.csvColumns[3]],
                        'mail': element[this.csvColumns[4]],
                        'phone': element[this.csvColumns[5]]
                    });
                });
                console.log(dataToSend);
            }),
            /*exhaustMap(() => this.http.delete(`../rest/listTemplates/${this.currentEntity.listTemplate.id}`)),
            tap(() => {

            }),*/
            catchError((err: any) => {
                this.loading = false;
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }
}
