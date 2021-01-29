import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';
import { NotificationService } from '@service/notification/notification.service';
import { Router } from '@angular/router';

@Component({
    selector: 'app-tile-view-resume',
    templateUrl: 'tile-view-resume.component.html',
    styleUrls: ['tile-view-resume.component.scss'],
})
export class TileViewResumeComponent implements OnInit, AfterViewInit {

    @Input() countResources: any[];
    @Input() icon: string = '';
    @Input() resourceLabel: string = '';
    @Input() route: string = null;
    @Input() extraParams: any = {};

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
        private router: Router,
        private notify: NotificationService
    ) { }

    ngOnInit(): void { }

    ngAfterViewInit(): void { }

    goTo() {
        const regex = /:\w*/g;
        const res = this.route.match(regex);

        let formatedRoute = this.route;
        const errors = [];

        if (res !== null) {
            res.forEach(elem => {
                const value = this.extraParams[elem.replace(':', '')];

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
