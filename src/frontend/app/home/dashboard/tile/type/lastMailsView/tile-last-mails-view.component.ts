import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';
import { Time } from '@angular/common';
import { TimeAgoPipe } from '@plugins/timeAgo.pipe';
import { catchError, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';

@Component({
    selector: 'app-tile-last-mails-view',
    templateUrl: 'tile-last-mails-view.component.html',
    styleUrls: ['tile-last-mails-view.component.scss'],
    providers: [TimeAgoPipe]
})
export class TileLastMailsViewDashboardComponent implements OnInit, AfterViewInit {

    @Input() view: string = 'list';

    @Input() tile: any = null;

    loading: boolean = true;
    testDate = new Date();

    label: 'Mes derniers courriers consultés';

    resources: any[] = [];
    countResources: number = 0;
    route: string = null;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
        private notify: NotificationService,
    ) { }

    ngOnInit(): void { }

    async ngAfterViewInit(): Promise<void> {
        await this['get_' + this.view]();
        this.route = this.tile.views.find((viewItem: any) => viewItem.id === this.view).route;
        this.loading = false;
    }

    async changeView(view: string, extraParams: any) {
        this.view = null;
        this.loading = true;
        await this['get_' + view](extraParams);
        this.view = view;
        this.route = this.tile.views.find((viewItem: any) => viewItem.id === this.view).route;
        this.loading = false;
    }

    get_list(extraParams: any) {
        return new Promise((resolve) => {
            // FOR TEST
            setTimeout(() => {
                this.resources = [
                    {
                        resId: 100,
                        recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                        subject: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque mattis eros non tellus elementum, a imperdiet lacus ornare. Nunc tincidunt nec massa sed cursus. Vivamus sit amet semper odio. Phasellus sed eleifend purus. Cras arcu ligula, sodales mollis nulla et, mollis sodales risus. Ut id nibh posuere, malesuada leo ac, consectetur neque. Suspendisse fringilla leo eget volutpat malesuada. Ut dictum, lectus in interdum aliquam, tellus ipsum euismod eros, et eleifend enim lorem non orci. Praesent tristique elit ac volutpat condimentum. Nullam pharetra, nunc ut imperdiet posuere, nisi ipsum molestie neque, sed tempus ex mauris sit amet lectus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Donec ac dapibus lacus, ac ultricies metus. Morbi tempus facilisis ex, eu malesuada massa laoreet et. Morbi pharetra at arcu nec vestibulum.',
                        creationDate: '2021-01-29 12:16:53.491567',
                    },
                    {
                        resId: 100,
                        recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                        subject: 'Réservation bal des basketteurs',
                        creationDate: '2021-01-29 12:16:53.491567',
                    },
                    {
                        resId: 100,
                        recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                        subject: 'Réservation bal des basketteurs',
                        creationDate: '2021-01-29 12:16:53.491567',
                    },
                    {
                        resId: 100,
                        recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                        subject: 'Réservation bal des basketteurs',
                        creationDate: '2021-01-29 12:16:53.491567',
                    },
                    {
                        resId: 100,
                        recipient: 'Jan-louis ERCOLANNI (MAARCH)',
                        subject: 'Réservation bal des basketteurs',
                        creationDate: '2021-01-29 12:16:53.491567',
                    }
                ];
                resolve(true);
            }, 3000);

            /*this.http.get('../rest/???').pipe(
                tap((data: any) => {
                    this.resources = data;
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();*/
        });
    }

    get_resume(extraParams: any) {
        return new Promise((resolve) => {
            // FOR TEST
            setTimeout(() => {
                this.countResources = 23;
                resolve(true);
            }, 3000);

            /*this.http.get('../rest/???').pipe(
                tap((data: any) => {
                    this.countResources = data;
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();*/
        });
    }

    get_chart(extraParams: any) {
        return new Promise((resolve) => {
            // FOR TEST
            setTimeout(() => {
                this.resources = [

                    {
                        name: 'Litige',
                        value: 24
                    },
                    {
                        name: 'Convocation',
                        value: 12
                    },
                    {
                        name: 'Abonnement',
                        value: 53
                    },
                    {
                        name: 'Réservation',
                        value: 21
                    },
                    {
                        name: 'Invitation',
                        value: 2
                    }
                ];
                resolve(true);
            }, 3000);

            /*this.http.get('../rest/???').pipe(
                tap((data: any) => {
                    this.resources = data;
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();*/
        });
    }
}
