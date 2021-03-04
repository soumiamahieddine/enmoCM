import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';

declare const Office: any;
const headers = new HttpHeaders({
    'Authorization': 'Basic ' + btoa('ppetit:maarch')
});
@Component({
    selector: 'app-test',
    templateUrl: './test.component.html',
    styleUrls: ['./test.component.scss']
})
export class TestComponent implements OnInit {
    
    myDoc: any = {
        "modelId" : 1,
        "doctype" : 102,
        "subject" : "Maarch Office 365",
        "chrono" : true,
        "typist" : 19,
        "status" : "COU",
        "destination" : 21,
        "initiator" : null,
        "priority" : "poiuytre1357nbvc",
        "documentDate" : "2020-01-01 17:18:47",
        "arrivalDate": "2020-01-01 18:18:40",
        "format" : "PDF",
        "encodedFile": "JVBERi0xLjQgLi4u",
        "externalId" : {"companyId" : "123456789"},
        "customFields" : {"2" : "ma valeur custom"},
        "senders" : [{"id" : 10, "type" : "contact"}],
        "recipients" : [],
        "folders" : [],
        "tags" : []
    }

    docFromMail: any = {};
    userInfos : any;
    mailBody: any

    constructor(
        public http: HttpClient,
    ) { }

    ngOnInit(): void {
        console.log(Office.context.mailbox.userProfile.emailAddress);        
             
        this.http.get('../rest/currentUser/profile', { headers: headers }).pipe(
            tap((data: any) => {
                this.userInfos = data;
                console.log(data);
            }),
            catchError((err: any) => {
                console.log(err);

                return of(false);
            })
        ).subscribe();

        this.getMailBody();
    }

    createDoc() {
        return new Promise((resolve) => {
            this.http.post('../rest/resources', this.myDoc, { headers: headers }).pipe(
                tap((data: any) => {
                    console.log(data);
                    resolve(true);
                }),
                catchError((err: any) => {
                    console.log(err);
                    return of(false);
                })
            ).subscribe();
        })
    }

    createDocFromMail() {
        this.docFromMail = {
            "modelId" : 1,
            "doctype" : 102,
            "subject" : Office.context.mailbox.item.subject,
            "chrono" : true,
            "typist" : this.userInfos.id,
            "status" : "NEW",
            "destination" : 21,
            "initiator" : null,
            "priority" : "poiuytre1357nbvc",
            "documentDate" : Office.context.mailbox.item.dateTimeCreated,
            "arrivalDate": Office.context.mailbox.item.dateTimeCreated,
            "format" : "TXT",
            "encodedFile": btoa(unescape(encodeURIComponent(this.mailBody))),
            "externalId" : {"emailId" : Office.context.mailbox.item.itemId},
            "customFields" : {"2" : "ma valeur custom"},
            "senders" : [{"id" : 10, "type" : "contact"}],
            "recipients" : [],
            "folders" : [],
            "tags" : []
        };
        return new Promise((resolve) => {
            return new Promise((resolve) => {
                this.http.post('../rest/resources', this.docFromMail, { headers: headers }).pipe(
                    tap((data: any) => {
                        console.log(data);
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        console.log(err);
                        return of(false);
                    })
                ).subscribe();
            })
        })
    }

    getMailBody() {
        Office.context.mailbox.item.body.getAsync(Office.CoercionType.Text, ((res: { value: any; }) => {
            this.mailBody = res.value;
            console.log(this.mailBody);
        }));  
    }
}
