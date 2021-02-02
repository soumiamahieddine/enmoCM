import { Component, OnInit, AfterViewInit, Input } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { AppService } from '@service/app.service';
import { DashboardService } from '@appRoot/home/dashboard/dashboard.service';

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
    @Input() tile: any;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public appService: AppService,
        private dashboardService: DashboardService,
    ) { }

    ngOnInit(): void { }

    ngAfterViewInit(): void { }

    goTo() {
        const data = { ...this.tile.parameters, userId: this.tile.userId };
        if (this.tile.maarchParapheurUrl !== undefined) {
            data['maarchParapheurUrl'] = this.tile.maarchParapheurUrl;
        }
        this.dashboardService.goTo(this.route, data);
    }
}
