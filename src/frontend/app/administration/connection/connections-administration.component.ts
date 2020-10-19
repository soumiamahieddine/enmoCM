import { Component, OnInit } from '@angular/core';
import { AppService } from '@service/app.service';

@Component({
    selector: 'app-connections-administration',
    templateUrl: './connections-administration.component.html',
    styleUrls: ['./connections-administration.component.scss']
})
export class ConnectionsAdministrationComponent implements OnInit {

    loading: boolean = true;

    constructor(
        public appService: AppService,
    ) { }

    ngOnInit(): void {
    }

}
