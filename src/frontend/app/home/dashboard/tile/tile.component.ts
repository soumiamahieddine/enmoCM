import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';
import { DashboardService } from '@appRoot/home/dashboard/dashboard.service';

@Component({
    selector: 'app-tile',
    templateUrl: 'tile.component.html',
    styleUrls: ['tile.component.scss']
})
export class TileDashboardComponent implements OnInit, AfterViewInit {

    @Input() view: string = 'list';

    @Input() tile: any = null;

    loading: boolean = true;

    resources: any[] = [];
    countResources: number = 0;
    route: string = null;
    viewDocRoute: string = null;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
        public dashboardService: DashboardService,
    ) { }

    ngOnInit(): void { }

    async ngAfterViewInit(): Promise<void> {
        await this['get_' + this.view]();
        this.route = this.tile.views.find((viewItem: any) => viewItem.id === this.view).route;
        this.viewDocRoute = this.tile.views.find((viewItem: any) => viewItem.id === this.view).viewDocRoute;
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

    async get_list(extraParams: any) {
        this.resources = await this.dashboardService.get_list(this.tile.id, extraParams);
    }

    async get_resume(extraParams: any) {
        this.countResources = await this.dashboardService.get_resume(this.tile.id, extraParams);
    }

    async get_chart(extraParams: any) {
        this.resources = await this.dashboardService.get_chart(this.tile.id, extraParams);
    }
}
