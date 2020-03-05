import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../app/translate.component';
import { tap, catchError, map } from 'rxjs/operators';
import { of } from 'rxjs';
import { MatSidenav } from '@angular/material';
import { FoldersService } from '../app/folder/folders.service';

@Injectable()
export class HeaderService {
    hideSideBar: boolean = false;
    sideNavLeft: MatSidenav = null;
    defaultSideNavLeft: MatSidenav = null;
    currentBasketInfo: any = {
        ownerId: 0,
        groupId: 0,
        basketId: ''
    };
    folderId: number = 0;
    headerMessageIcon: string = "";
    headerMessage: string = "";
    subHeaderMessage: string = "";
    user: any = { firstname: "", lastname: "", groups: [], privileges: [] };
    lang: any = LANG;
    nbResourcesFollowed: number = 0;
    base64: string = null;

    constructor(
        public http: HttpClient,
        public foldersService: FoldersService,
    ) { }

    loadHeader() {
        return new Promise((resolve, reject) => {
            this.http.get('../../rest/header').pipe(
                tap((data: any) => {
                    this.setUser(data.user);
                    resolve(true);
                }),
                catchError((err: any) => {
                    console.log(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });

    }

    resfreshCurrentUser() {
        return new Promise((resolve, reject) => {
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
                        };
                        this.nbResourcesFollowed = data.nbFollowedResources;
                        resolve(true);
                    })
                ).subscribe();
        });

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

    resetSideNavSelection() { 
        console.log(this.defaultSideNavLeft);
               
        if (this.defaultSideNavLeft !== undefined) {
            this.sideNavLeft = this.defaultSideNavLeft;
            this.sideNavLeft.open();
            this.currentBasketInfo = {
                ownerId: 0,
                groupId: 0,
                basketId: ''
            };
            this.foldersService.setFolder({ id: 0 });
        }
        
    }
}
