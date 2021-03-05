import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';

declare const Office: any;
@Component({
    selector: 'app-test',
    templateUrl: './test.component.html',
    styleUrls: ['./test.component.scss']
})
export class TestComponent implements OnInit {
    loading: boolean = false;

    // must be REST USER (with create_contact privilege)
    headers = new HttpHeaders({
        Authorization: 'Basic ' + btoa('cchaplin:maarch')
    });

    applicationName: string = 'Maarch Courrier';

    myDoc: any = {
        modelId: 1,
        doctype: 102,
        subject: 'Maarch Office 365',
        chrono: true,
        typist: 19,
        status: 'COU',
        destination: 21,
        initiator: null,
        priority: 'poiuytre1357nbvc',
        documentDate: '2020-01-01 17:18:47',
        arrivalDate: '2020-01-01 18:18:40',
        format: 'PDF',
        encodedFile: 'JVBERi0xLjQgLi4u',
        externalId: { companyId: '123456789' },
        customFields: { 2: 'ma valeur custom' },
        senders: [{ id: 10, type: 'contact' }],
        recipients: [],
        folders: [],
        tags: []
    };

    docFromMail: any = {};
    contactInfos: any = {};
    userInfos: any;
    mailBody: any;
    contactId: number;

    constructor(
        public http: HttpClient,
    ) { }

    async ngOnInit(): Promise<void> {
        console.log(Office.context.mailbox.item);

        await this.createContact();

        this.http.get('../rest/authenticationInformations')
            .pipe(
                tap((data: any) => {
                    // console.log(data);
                    this.applicationName = data.applicationName;
                }),
                catchError((err: any) => {
                    console.log(err);
                    return of(false);
                })
            ).subscribe();
            
        this.getMailBody();
    }

    async sendToMaarch() {
        this.loading = true;
        await this.createContact();
        this.createDocFromMail();
    }

    createDocFromMail() {
        this.docFromMail = {
            modelId: 1,
            doctype: 102,
            subject: Office.context.mailbox.item.subject,
            chrono: true,
            // typist : 10,
            status: 'NEW',
            documentDate: Office.context.mailbox.item.dateTimeCreated,
            arrivalDate: Office.context.mailbox.item.dateTimeCreated,
            format: 'TXT',
            encodedFile: btoa(unescape(encodeURIComponent(this.mailBody))),
            externalId: { emailId: Office.context.mailbox.item.itemId },
            senders: [{ id: this.contactId, type: 'contact' }]
        };
        return new Promise((resolve) => {
            return new Promise((resolve) => {
                this.http.post('../rest/resources', this.docFromMail, { headers: this.headers }).pipe(
                    tap((data: any) => {
                        // console.log(data);
                        resolve(true);
                    }),
                    finalize(() => this.loading = false),
                    catchError((err: any) => {
                        console.log(err);
                        return of(false);
                    })
                ).subscribe();
            });
        });
    }

    getMailBody() {
        Office.context.mailbox.item.body.getAsync(Office.CoercionType.Text, ((res: { value: any; }) => {
            this.mailBody = res.value;
            // console.log(this.mailBody);
        }));
    }

    createContact() {
        const userName: string = Office.context.mailbox.item.from.displayName;
        const index = userName.lastIndexOf(' ');
        this.contactInfos = {
            firstname: userName.substring(0, index),
            lastname: userName.substring(index + 1),
            email: Office.context.mailbox.item.from.emailAddress,
        };
        return new Promise((resolve) => {
            this.http.post('../rest/contacts', this.contactInfos, { headers: this.headers }).pipe(
                tap((data: any) => {
                    // console.log(data.id);
                    this.contactId = data.id;
                    resolve(true);
                }),
                catchError((err: any) => {
                    console.log(err);
                    return of(false);
                })
            ).subscribe();
        });
    }
}
