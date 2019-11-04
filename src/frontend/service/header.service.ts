import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../app/translate.component';
import { tap, catchError, filter } from 'rxjs/operators';
import { of } from 'rxjs';

@Injectable()
export class HeaderService {
    headerMessageIcon: string = "";
    headerMessage: string = "";
    subHeaderMessage: string = "";
    user: any = { firstname: "", lastname: "" };
    lang: any = LANG;

    constructor(public http: HttpClient) { }

    loadHeader() {
        this.http.get('../../rest/header').pipe(
            tap((data: any) => this.setUser(data.user)),
            catchError((err: any) => {
                console.log(err);
                return of(false);
            })
        ).subscribe();
    }

    setUser(user: any) {
        this.user = user;
    }

    setHeader(maintTitle: string, subTitle: any = '', icon = '') {
        this.headerMessage = maintTitle;
        this.subHeaderMessage = subTitle;
        this.headerMessageIcon = icon;
    }
}
