import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../app/translate.component';
import { tap, catchError, map } from 'rxjs/operators';
import { of } from 'rxjs';

@Injectable()
export class HeaderService {
    headerMessageIcon: string = "";
    headerMessage: string = "";
    subHeaderMessage: string = "";
    user: any = { firstname: "", lastname: "", groups : [], privileges : [] };
    lang: any = LANG;
    nbResourcesFollowed: number = 0;
    base64: string = null;

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

    resfreshCurrentUser() {
        this.http.get('../../rest/currentUser/profile')
            .pipe(
                map((data: any) => {
                    this.user = {
                        id: data.id,
                        userId: data.user_id,
                        firstname: data.firstname,
                        lastname: data.lastname,
                        entities: data.entities,
                        groups: data.groups,
                        preferences: data.preferences,
                        privileges: data.privileges[0] === 'ALL_PRIVILEGES' ? this.user.privileges : data.privileges
                    }
                })
            ).subscribe();
    }

    setUser(user: any) {
        this.user = user;
    }

    getLastLoadedFile() {
        return this.base64;
    }

    setLoadedFile(base64: string) {
        this.base64 = base64;
    }

    setHeader(maintTitle: string, subTitle: any = '', icon = '') {
        this.headerMessage = maintTitle;
        this.subHeaderMessage = subTitle;
        this.headerMessageIcon = icon;
    }
}
