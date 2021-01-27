import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';

@Component({
    selector: 'app-tile-view-chart',
    templateUrl: 'tile-view-chart.component.html',
    styleUrls: ['tile-view-chart.component.scss'],
})
export class TileViewChartComponent implements OnInit, AfterViewInit {

    @Input() icon: string = '';

    saleData = [
        { name: 'Litige', value: 24 },
        { name: 'Convocation', value: 12 },
        { name: 'Abonnement', value: 53 },
        { name: 'Réservation', value: 21 },
        { name: 'Invitation', value: 2 }
    ];

    testDate = new Date();

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
    ) { }

    ngOnInit(): void { }

    ngAfterViewInit(): void { }
}
