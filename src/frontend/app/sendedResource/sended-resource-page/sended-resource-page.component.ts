import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { Component, OnInit, ViewChild, EventEmitter, ElementRef, Input, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { Observable, merge, Subject, of as observableOf, of } from 'rxjs';
import { MatPaginator, MatSort, MatDialog, MatTableDataSource, MAT_DIALOG_DATA, MatDialogRef, MatChipInputEvent } from '@angular/material';
import { takeUntil, startWith, switchMap, map, catchError, filter, exhaustMap, tap, debounceTime, distinctUntilChanged, finalize } from 'rxjs/operators';
import { FormControl } from '@angular/forms';

@Component({
    selector: 'app-sended-resource-page',
    templateUrl: "sended-resource-page.component.html",
    styleUrls: ['sended-resource-page.component.scss'],
})
export class SendedResourcePageComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = true;

    readonly separatorKeysCodes: number[] = [ENTER, COMMA];

    availableSenders: any[] = ['alex.orluc@maarch.org', 'support@maarch.org'];

    recipients: any[] = [];

    copies: any[] = [];

    invisibleCopies: any[] = [];

    fruits: any[] = [];

    recipientsInput: FormControl = new FormControl();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<SendedResourcePageComponent>,
    ) { }

    async ngOnInit(): Promise<void> {

        this.loading = false;

        this.initEmailsList();

    }

    add(event: MatChipInputEvent, type: string): void {
        const input = event.input;
        const value = event.value;

        // Add our fruit
        if ((value || '').trim()) {
            this[type].push(value.trim());
        }

        // Reset the input value
        if (input) {
            input.value = '';
        }
    }

    remove(item: any, type: string): void {

        const index = this[type].indexOf(item);

        if (index >= 0) {
            this[type].splice(index, 1);
        }
    }

    initEmailsList() {
        this.recipientsInput.valueChanges.pipe(
            debounceTime(300),

            filter(value => value.length > 2),
            tap(() => this.loading = true),
            switchMap(data => this.http.get('../../rest/autocomplete/correspondents', { params: { "search": data , "searchEmails": 'true' } })),
            tap((data: any) => {
                console.log(data); 
            }),
            catchError((err) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();        
    }
}