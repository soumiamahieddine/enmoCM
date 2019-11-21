import { Component, Input, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MatDialog } from '@angular/material';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    selector: 'app-contacts-list',
    templateUrl: 'contacts-list.component.html',
    styleUrls: ['contacts-list.component.scss'],
    providers: [NotificationService]
})
export class ContactsListComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;

    contacts: any = [{
        mode: 'corporate',
        firstname: 'Jean',
        lastname: 'DUBOIS',
        email: 'jean.dubois@maarch.org',
        phone: '06 11 12 13 14',
        society: 'MAARCH',
        department: 'Pôle maarch Courrier',
        occupancy: '1er étage',
        function: 'Développeur',
        num: '11',
        street: 'boulevard du sud-est',
        complement: 'portail vert',
        town: 'NANTERRE',
        postalCode: '92000',
        country: 'FRANCE',
        otherData: 'Editeur du logiciel libre Maarch',
        website: 'www.maarchcourrier.com',
        filling: '#ff5733'
    },
    {
        mode: 'internal',
        firstname: 'Bernard',
        lastname: 'BLIER',
        email: 'bblier@maarch.org',
        phone: '06 11 12 13 14',
        society: '',
        department: 'Pôle maarch Courrier',
        occupancy: '',
        function: '',
        num: '',
        street: '',
        complement: '',
        town: '',
        postalCode: '',
        country: '',
        otherData: '',
        website: '',
        filling: '#ff5733'
    },
    {
        mode: 'entity',
        firstname: '',
        lastname: 'Pôle Jeunesse et Sport',
        email: 'PJS@maarch.org',
        phone: '',
        society: '',
        department: '',
        occupancy: '',
        function: '',
        num: '',
        street: '',
        complement: '',
        town: '',
        postalCode: '',
        country: '',
        otherData: '',
        website: '',
        filling: ''
    },
    {
        mode: 'third',
        firstname: 'Jean',
        lastname: 'DUBOIS',
        email: 'jean.dubois@maarch.org',
        phone: '06 11 12 13 14',
        society: 'MAARCH',
        department: 'Pôle maarch Courrier',
        occupancy: '1er étage',
        function: 'Développeur',
        num: '11',
        street: 'boulevard du sud-est',
        complement: 'portail vert',
        town: 'NANTERRE',
        postalCode: '92000',
        country: 'FRANCE',
        otherData: 'Editeur du logiciel libre Maarch',
        website: 'www.maarchcourrier.com',
        filling: '#ff5733'
    }];

    /**
     * Ressource identifier to load contact List
     */
    @Input('resId') resId: number;
    @Input('mode') mode: 'recipients' | 'senders';

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
    ) { }

    ngOnInit(): void {
        // TO DO : WAIT BACK
        this.loading = false;
        /*this.http.get(`../../rest/resources/${this.resId}/contacts?type=${this.mode}`).pipe(
            tap((data: any) => {
                this.contacts = data.contacts;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();*/
    }

    goTo(contact: any) {
        window.open(`https://www.google.com/maps/search/${contact.num}+${contact.street},+${contact.postalCode}+${contact.town},+${contact.country}`,'_blank')
    }
}