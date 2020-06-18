import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ValidationErrors } from '@angular/forms';
import { NotificationService } from '../../notification.service';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/internal/operators/tap';
import { catchError } from 'rxjs/internal/operators/catchError';
import { of } from 'rxjs/internal/observable/of';
import { LANG } from '../../translate.component';

@Component({
    selector: 'app-database',
    templateUrl: './database.component.html',
    styleUrls: ['./database.component.scss']
})
export class DatabaseComponent implements OnInit {
    lang: any = LANG;
    stepFormGroup: FormGroup;

    connectionState: boolean = false;

    constructor(
        public http: HttpClient,
        private _formBuilder: FormBuilder,
        private notify: NotificationService,
    ) {
        this.stepFormGroup = this._formBuilder.group({
            dbHostCtrl: ['localhost', Validators.required],
            dbLoginCtrl: ['', Validators.required],
            dbPortCtrl: ['5432', Validators.required],
            dbPasswordCtrl: ['', Validators.required],
            dbNameCtrl: ['', Validators.required],
            stateStep: ['', Validators.required]
        });
    }

    ngOnInit(): void { }

    isValidConnection() {
        return false;
    }

    checkConnection() {

        const info = {
            server: this.stepFormGroup.controls['dbHostCtrl'].value,
            port: this.stepFormGroup.controls['dbPortCtrl'].value,
            user: this.stepFormGroup.controls['dbLoginCtrl'].value,
            password: this.stepFormGroup.controls['dbPasswordCtrl'].value,
            name: this.stepFormGroup.controls['dbNameCtrl'].value
        };

        this.http.get(`../rest/installer/databaseConnection`, { params: info }).pipe(
            tap((data: any) => {
                this.notify.success(this.lang.rightInformations);
                this.stepFormGroup.controls['stateStep'].setValue('success');
            }),
            catchError((err: any) => {
                this.notify.error(this.lang.badInformations);
                this.stepFormGroup.markAllAsTouched();
                this.stepFormGroup.controls['stateStep'].setValue('');
                return of(false);
            })
        ).subscribe();
    }

    checkStep() {
        return this.stepFormGroup.valid;
    }

    isValidStep() {
        /*Object.keys(this.stepFormGroup.controls).forEach(key => {

            const controlErrors: ValidationErrors = this.stepFormGroup.get(key).errors;
            if (controlErrors != null) {
                Object.keys(controlErrors).forEach(keyError => {
                    console.log('Key control: ' + key + ', keyError: ' + keyError + ', err value: ', controlErrors[keyError]);
                });
            }
        });*/
        return this.stepFormGroup === undefined ? false : this.stepFormGroup.valid;
    }

    getFormGroup() {
        return this.stepFormGroup;
    }

}
