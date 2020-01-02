import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of, Subject } from 'rxjs';

@Component({
    selector: 'app-contacts-list',
    templateUrl: 'contacts-list.component.html',
    styleUrls: ['contacts-list.component.scss'],
    providers: [NotificationService]
})
export class ContactsListComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;

    contacts: any = [];

    /**
     * Ressource identifier to load contact List
     */
    @Input() resId: number = null;

    /**
     * [Filter to load specific contact Type]
     * use with @resId
     */
    @Input() mode: 'recipients' | 'senders' = 'recipients';

    /**
     * [Id of contact to load a specific resource]
     * DO NOT USE with @resId
     * ex : {id: 1, type: 'contact'}
     */
    @Input() contact: any = null;

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
    ) { }

    ngOnInit(): void {
        this.loading = false;

        if (this.resId !== null) {
            this.loadContactsOfResource(this.resId, this.mode);
        } else if (this.contact !== null) {
            this.loadContact(this.contact.id, this.contact.type);
        }
    }

    loadContactsOfResource(resId: number, mode: string) {
        this.http.get(`../../rest/resources/${resId}/contacts?type=${mode}`).pipe(
            tap((data: any) => {
                this.contacts = data.contacts;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    loadContact(contactId: number, type: string) {
        this.contacts = [];

        if (type === 'contact') {
            this.http.get('../../rest/contacts/' + contactId).pipe(
                tap((contact: any) => {
                    this.contacts[0] = {
                        ...contact,
                        civilityShortLabel: '',
                        civilityLabel: '',
                        type: 'contact'
                    };
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else if (type === 'user') {
            this.http.get('../../rest/users/' + contactId).pipe(
                tap((data: any) => {
                    this.contacts[0] = {
                        type: 'user',
                        civilityShortLabel: '',
                        civilityLabel: '',
                        firstname: data.firstname,
                        lastname: data.lastname,
                    };
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else if (type === 'entity') {
            this.http.get('../../rest/entities/' + contactId).pipe(
                tap((data: any) => {
                    this.contacts[0] = {
                        type: 'entity',
                        civilityShortLabel: '',
                        civilityLabel: '',
                        lastname: data.short_label,
                    };
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.error(err.error.errors);
                    return of(false);
                })
            ).subscribe();
        }
    }

    goTo(contact: any) {
        window.open(`https://www.google.com/maps/search/${contact.addressNumber}+${contact.addressStreet},+${contact.addressPostcode}+${contact.addressTown},+${contact.addressCountry}`, '_blank')
    }

    empty(value: any) {
        if (value !== null && value !== '' && value !== undefined) {
            return false;
        } else {
            return true;
        }
    }

    emptyOtherInfo(contact: any) {
        if (!this.empty(contact.communicationMeans) || contact.customFields !== null) {
            return false;
        } else {
            return true;
        }
    }
}
