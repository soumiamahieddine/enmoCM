import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';

declare const Office: any;
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

    constructor(
        public http: HttpClient,
    ) { }

    ngOnInit(): void {
        console.log('Mail infos', Office.context.mailbox.item);
        const headers = new HttpHeaders({
            'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE2MTQ4NTgxODcsInVzZXIiOnsiaWQiOjEwLCJmaXJzdG5hbWUiOiJQYXRyaWNpYSIsImxhc3RuYW1lIjoiUEVUSVQiLCJzdGF0dXMiOiJPSyIsImxvZ2luIjoicHBldGl0In19.cjPuHtO8I7jmN0yomXV-Z6X4LknaJ28P5TSZ0gP2FjA'
        });

        this.http.get('../rest/currentUser/profile', { headers: headers }).pipe(
            tap((data: any) => {
                console.log(data);
            }),
            catchError((err: any) => {
                console.log(err);

                return of(false);
            })
        ).subscribe();
    }

    createDoc() {
        const headers = new HttpHeaders({
            'Authorization': 'Basic ' + btoa('ppetit:maarch')
        });
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
}
