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
    menu: any[] = [];
    shortcut: any[] = null;
    shortcutIcon: any = {
        home: 'fa fa-home',
        administration: 'fa fa-cogs',
        search: 'fa fa-search',
        indexing: 'fa fa-file-medical',
    };
    lang: any = LANG;

    constructor(public http: HttpClient) { }

    loadHeader() {
        this.http.get('../../rest/header').pipe(
            tap((data: any) => this.setUser(data.user)),
            tap((data: any) => this.setMenu(data.menu)),
            catchError((err: any) => {
                console.log(err);
                return of(false);
            })
        ).subscribe();
    }

    getShortcut() {
        if (this.shortcut === null) {
            this.http.get('../../rest/shortcuts').pipe(
                tap((data: any) => this.setShortcut(data.shortcuts)),
                catchError((err: any) => {
                    console.log(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    refreshShortcuts() {
        this.shortcut = null;
        this.getShortcut();
    }

    setUser(user: any) {
        this.user = user;
    }

    setMenu(menu: any) {

        menu.unshift({
            "name": this.lang.home,
            "comment": this.lang.home,
            "servicepage": "/home",
            "shortcut": "true",
            "style": "fa fa-home",
            "angular": "true"
        });

        this.menu = menu;

    }

    setShortcut(shortcuts: any) {
        this.shortcut = [];
        shortcuts.forEach((element: any) => {
            if (['search'].indexOf(element.id) > -1) {
                // TO DO : DELETE AFTER FULL V2
                this.setShortcutV1(element);
            } else {
                this.shortcut.push(
                    {
                        id: element.id,
                        name: this.lang[element.id],
                        servicepage:  '/' + element.id,
                        style: this.shortcutIcon[element.id],
                        angular: element.id !== 'search' ? "true" : "false",
                        groups : element.groups !== undefined ? element.groups : ''
                    },
                );
            }
        });
    }

    setShortcutV1(shortcut: any) {
        this.shortcut.push(
            {
                id: shortcut.id,
                name: this.lang[shortcut.id],
                servicepage:  shortcut.id !== 'search' ? "index.php?page=view_baskets&module=basket&baskets=IndexingBasket" : "index.php?page=search_adv&dir=indexing_searching",
                style: this.shortcutIcon[shortcut.id],
                angular: "false",
                groups : shortcut.groups !== undefined ? shortcut.groups : ''
            },
        );
    }

    setHeader(maintTitle: string, subTitle: any = '', icon = '') {
        this.headerMessage = maintTitle;
        this.subHeaderMessage = subTitle;
        this.headerMessageIcon = icon;
    }
}
