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
    @Input() resources: any[];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
    ) { }

    ngOnInit(): void {
        console.log(this.resources);
    }

    ngAfterViewInit(): void { }
}
