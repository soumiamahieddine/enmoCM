import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { tap, catchError } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { of } from 'rxjs';

@Component({
    selector: 'app-life-cyle',
    templateUrl: './life-cycle.component.html',
})

export class LifeCycleComponent implements OnInit {
    finalActionValues: any[] = ['restrictAccess', 'transfer', 'copy', 'delete'];
    bindingDocumentFinalAction: any[] = [];
    nonBindingDocumentFinalAction: any[] = [];
    notify: any;

    constructor(public translate: TranslateService, public http: HttpClient) {}

    async ngOnInit(): Promise<void> {
        await this.getFinalAction();
    }

    getFinalAction() {
        return new Promise((resolve) => {
            this.http.get('../rest/parameters').pipe(
                tap((data: any) => {
                        const bindDocumentFinalAction = data.parameters.filter((t: { id: any; }) => t.id === 'bindingDocumentFinalAction');
                        const nonBindDocumentFinalAction = data.parameters.filter((t: { id: any; }) => t.id === 'nonBindingDocumentFinalAction');
                        this.bindingDocumentFinalAction = bindDocumentFinalAction[0];
                        this.nonBindingDocumentFinalAction = nonBindDocumentFinalAction[0];
                        resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

}
