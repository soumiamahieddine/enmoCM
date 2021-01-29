import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';
import { Router } from '@angular/router';
import { NotificationService } from '@service/notification/notification.service';

@Component({
    selector: 'app-tile-view-list',
    templateUrl: 'tile-view-list.component.html',
    styleUrls: ['tile-view-list.component.scss'],
})
export class TileViewListComponent implements OnInit, AfterViewInit {

    @Input() displayColumns: string[];

    @Input() resources: any[];
    @Input() icon: string = '';
    @Input() route: string = null;

    thumbnailUrl: string = '';
    testDate = new Date();

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
        private router: Router,
        private notify: NotificationService
    ) { }

    ngOnInit(): void { }

    ngAfterViewInit(): void { }

    viewThumbnail(ev: any, resource: any) {
        const timeStamp = +new Date();
        this.thumbnailUrl = '../rest/resources/' + resource.resId + '/thumbnail?tsp=' + timeStamp;
        $('#viewThumbnail').show();
        console.log(ev);
    }

    closeThumbnail() {
        $('#viewThumbnail').hide();
    }

    goTo(resource: any) {
        const regex = /:\w*/g;
        const res = this.route.match(regex);

        let formatedRoute = this.route;
        const errors = [];

        if (res !== null) {
            res.forEach(elem => {
                const value = resource[elem.replace(':', '')];

                if (value !== undefined) {
                    formatedRoute = formatedRoute.replace(elem, value);
                } else {
                    errors.push(elem);
                }
            });
        }


        if (errors.length === 0) {
            this.router.navigate([formatedRoute]);
        } else {
            this.notify.error(errors + ' not found');
        }
    }
}
