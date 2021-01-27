import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';

@Component({
    selector: 'app-tile-view-list',
    templateUrl: 'tile-view-list.component.html',
    styleUrls: ['tile-view-list.component.scss'],
})
export class TileViewListComponent implements OnInit, AfterViewInit {

    @Input() displayColumns: string[];

    @Input() resources: any[];
    @Input() icon: string = '';

    testDate = new Date();

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
    ) { }

    ngOnInit(): void { }

    ngAfterViewInit(): void { }
}
