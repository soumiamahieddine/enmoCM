import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { HeaderService } from '../../../service/header.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { MatSidenav } from '@angular/material/sidenav';
import { AppService } from '../../../service/app.service';
import { tap, catchError, filter, exhaustMap, map, finalize } from 'rxjs/operators';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';
import { of } from 'rxjs';
import { SortPipe } from '../../../plugins/sorting.pipe';

declare function $j(selector: any): any;

@Component({
    templateUrl: "custom-fields-administration.component.html",
    styleUrls: ['custom-fields-administration.component.scss'],
    providers: [NotificationService, AppService, SortPipe]
})

export class CustomFieldsAdministrationComponent implements OnInit {

    @ViewChild('snav', { static: true }) public sidenavLeft: MatSidenav;
    @ViewChild('snav2', { static: true }) public sidenavRight: MatSidenav;

    lang: any = LANG;

    loading: boolean = false;
    creationMode: any = false;

    customFieldsTypes: any[] = [
        {
            label: this.lang.stringInput,
            type: 'string'
        },
        {
            label: this.lang.selectInput,
            type: 'select'
        },
        {
            label: this.lang.dateInput,
            type: 'date'
        },
        {
            label: this.lang.radioInput,
            type: 'radio'
        },
        {
            label: this.lang.checkboxInput,
            type: 'checkbox'
        }
    ]
    customFields: any[] = [
        {
            id: 4,
            label: 'Nouveau champ',
            type: 'select',
            values: [],
            default_value: ''
        }
    ];

    customFieldClone: any;

    dialogRef: MatDialogRef<any>;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog,
        private headerService: HeaderService,
        public appService: AppService,
        private sortPipe: SortPipe
    ) {

    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.administration + ' ' + this.lang.customFields);
        window['MainHeaderComponent'].setSnav(this.sidenavLeft);
        window['MainHeaderComponent'].setSnavRight(this.sidenavRight);

        this.http.get("../../rest/customFields").pipe(
            // TO FIX DATA BINDING SIMPLE ARRAY VALUES
            map((data: any) => {
                data.customFields.forEach((element: any) => {
                    if (element.values != null) {
                        element.values = element.values.map((info: any) => {
                            return {
                                label: info
                            }
                        });
                    }
                });
                return data;
            }),
            tap((data: any) => {
                this.customFields = data.customFields;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    addCustomField(customFieldType: any) {

        let newCustomField: any = {};

        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.add, msg: this.lang.confirmAction } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            tap(() => {
                newCustomField = {
                    label: 'Nouveau champ',
                    type: customFieldType.type,
                    values: [],
                    default_value: ''
                }
            }),
            exhaustMap((data) => this.http.post('../../rest/customFields', newCustomField)),
            tap((data: any) => {
                newCustomField.id = data.customFieldId
                this.customFields.push(newCustomField);
                this.notify.success(this.lang.customFieldAdded);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    addValue(indexCustom: number) {
        this.customFields[indexCustom].values.push(
            {
                label: 'Nouvelle donnée'
            }
        );
    }

    removeValue(customField: any, indexValue: number) {
        customField.values.splice(indexValue, 1);
    }

    removeCustomField(indexCustom: number) {
        this.dialogRef = this.dialog.open(ConfirmComponent, { autoFocus: false, disableClose: true, data: { title: this.lang.delete + ' "' + this.customFields[indexCustom].label + '"', msg: this.lang.confirmAction } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            tap(() => {
                this.customFields.splice(indexCustom, 1);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    updateCustomField(customField: any) {

        // TO FIX DATA BINDING SIMPLE ARRAY VALUES
        const customFieldToUpdate = { ...customField };
        if (customField.values != null) {
            customFieldToUpdate.values = customField.values.map((data: any) => data.label)
        }

        this.http.put('../../rest/customFields/' + customField.id, customFieldToUpdate).pipe(
            tap(() => {
                this.notify.success(this.lang.customFieldUpdated);
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    sortValues(customField: any) {
        customField.values = this.sortPipe.transform(customField.values, 'label');
    }

    initCustomFieldCheck(customField: any) {
        console.log('init');
        this.customFieldClone = JSON.parse(JSON.stringify(customField));
    }

    isModified(customField: any) {
        if (JSON.stringify(customField) === JSON.stringify(this.customFieldClone)) {
            return true;
        } else {
            return false;
        }
    }
}