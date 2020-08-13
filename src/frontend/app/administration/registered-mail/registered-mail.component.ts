import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder } from '@angular/forms';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { NotificationService } from '../../../service/notification/notification.service';
import { HeaderService } from '../../../service/header.service';
import { AppService } from '../../../service/app.service';
import { tap, catchError } from 'rxjs/operators';
import { of } from 'rxjs/internal/observable/of';

@Component({
    selector: 'app-registered-mail',
    templateUrl: './registered-mail.component.html',
    styleUrls: ['./registered-mail.component.scss']
})
export class RegisteredMailComponent implements OnInit {

    creationMode: boolean;
    loading: boolean = true;

    adminFormGroup: FormGroup;
    id: number = null;

    customerAccountNumberList: any[] = [];

    registeredMailType: any[] = [
        {
            id: '2D',
            label: 'National sans AR'
        },
        {
            id: '2C',
            label: 'National avec AR'
        },
        {
            id: 'RW',
            label: 'International'
        }
    ];

    constructor(
        private translate: TranslateService,
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        private _formBuilder: FormBuilder,
    ) { }

    ngOnInit(): void {
        this.route.params.subscribe(async (params) => {
            this.getIssuingSites();
            if (typeof params['id'] === 'undefined') {
                this.creationMode = true;
                this.headerService.setHeader(this.translate.instant('lang.registeredMailNumberRangesCreation'));

                this.adminFormGroup = this._formBuilder.group({
                    id: [null],
                    siteId: [null],
                    trackerNumber: [null],
                    registeredMailType: [null],
                    rangeStart: [1],
                    rangeEnd: [2],
                    status: ['SPD']
                });

                this.loading = false;

            } else {
                this.headerService.setHeader(this.translate.instant('lang.registeredMailNumberRangesModification'));
                this.id = params['id'];
                this.creationMode = false;
                await this.getData();
            }

            this.adminFormGroup.controls['rangeStart'].valueChanges.pipe(
                tap((value: string) => {
                    if (value > this.adminFormGroup.controls['rangeEnd'].value) {
                        this.adminFormGroup.controls['rangeStart'].setErrors({'rangeError': true});
                    } else {
                        this.adminFormGroup.controls['rangeStart'].setErrors(null);
                        this.adminFormGroup.controls['rangeEnd'].setErrors(null);
                    }
                })
            ).subscribe();

            this.adminFormGroup.controls['rangeEnd'].valueChanges.pipe(
                tap((value: string) => {
                    if (value < this.adminFormGroup.controls['rangeStart'].value) {
                        this.adminFormGroup.controls['rangeEnd'].setErrors({'rangeError': true});
                    } else {
                        this.adminFormGroup.controls['rangeStart'].setErrors(null);
                        this.adminFormGroup.controls['rangeEnd'].setErrors(null);
                    }
                })
            ).subscribe();
        });
    }

    getIssuingSites() {
        this.http.get('../rest/registeredMail/sites').pipe(
            tap((data: any) => {
                this.customerAccountNumberList = data['sites'].map((item: any) => {
                    return {
                        id: item.id,
                        label: `${item.label} (${item.accountNumber})`
                    };
                });
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getData() {
        return new Promise((resolve) => {
            this.http.get(`../rest/registeredMail/ranges/${this.id}`).pipe(
                tap((data: any) => {
                    this.adminFormGroup = this._formBuilder.group({
                        id: [this.id],
                        siteId: [data.range.siteId],
                        trackerNumber: [data.range.trackerNumber],
                        registeredMailType: [data.range.registeredMailType],
                        rangeStart: [data.range.rangeStart],
                        rangeEnd: [data.range.rangeEnd],
                        status: [data.range.status]
                    });
                    if (data.range.status === 'OK' || data.range.status === 'END') {
                        this.adminFormGroup.disable();
                    }
                    resolve(true);
                    this.loading = false;
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    onlyNumbers(event) {
        let k;
        k = event.charCode;
        if (this.adminFormGroup.controls['rangeStart'].value === null && k === 48) {
            return false;
        } else {
            return (k >= 48 && k <= 57);
        }
    }

    onSubmit() {
        const objToSubmit = {};
        Object.keys(this.adminFormGroup.controls).forEach(key => {
            objToSubmit[key] = this.adminFormGroup.controls[key].value;
        });

        if (this.creationMode) {
            this.http.post('../rest/registeredMail/ranges', objToSubmit)
                .subscribe(() => {
                    this.notify.success(this.translate.instant('lang.registeredMailNumberRangesAdded'));
                    this.router.navigate(['/administration/registeredMails']);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put('../rest/registeredMail/ranges/' + this.id, objToSubmit)
                .subscribe(() => {
                    this.notify.success(this.translate.instant('lang.registeredMailNumberRangesUpdated'));
                    this.router.navigate(['/administration/registeredMails']);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

}
