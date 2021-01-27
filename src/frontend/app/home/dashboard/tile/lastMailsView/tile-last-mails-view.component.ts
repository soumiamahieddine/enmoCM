import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';
import { Time } from '@angular/common';
import { TimeAgoPipe } from '@plugins/timeAgo.pipe';

@Component({
    selector: 'app-tile-last-mails-view',
    templateUrl: 'tile-last-mails-view.component.html',
    styleUrls: ['tile-last-mails-view.component.scss'],
    providers: [TimeAgoPipe]
})
export class TileLastMailsViewDashboardComponent implements OnInit, AfterViewInit {

    @Input() view: string = 'list';

    testDate = new Date();

    label: 'Mes derniers courriers consultés';

    resources: any[] = [];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
        private timeAgoPipe: TimeAgoPipe
    ) { }

    ngOnInit(): void { }

    ngAfterViewInit(): void {
        this.resources = [
            {
                recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                subject: 'Réservation bal des basketteurs',
                creationDate: '26-01-2021',
            },
            {
                recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                subject: 'Réservation bal des basketteurs',
                creationDate: '26-01-2021',
            },
            {
                recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                subject: 'Réservation bal des basketteurs',
                creationDate: '26-01-2021',
            },
            {
                recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                subject: 'Réservation bal des basketteurs',
                creationDate: '26-01-2021',
            },
            {
                recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                subject: 'Réservation bal des basketteurs',
                creationDate: '26-01-2021',
            }
        ];
    }
}
