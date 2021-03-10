import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { FormControl } from '@angular/forms';


@Component({
    selector: 'app-maarch-to-maarch-parameters',
    templateUrl: './maarch-to-maarch-parameters.component.html',
    styleUrls: ['./maarch-to-maarch-parameters.component.scss'],
})
export class MaarchToMaarchParametersComponent implements OnInit {

    basketToRedirect = new FormControl('NumericBasket');
    metadata: any = {
        typeId: new FormControl(101),
        status: new FormControl('NUMQUAL'),
        priority: new FormControl('poiuytre1357nbvc'),
        indexingModelId: new FormControl(1),
        attachmentType: new FormControl('simple_attachment'),
    };
    annuaryConf = {
        enabled: new FormControl(false),
        annuaries : []
    };
    constructor(
        public translate: TranslateService,
        public http: HttpClient,
    ) { }

    ngOnInit() {
    }

    addAnnuary() {
        this.annuaryConf.annuaries.push({
            uri: new FormControl('1.1.1.1'),
            baseDN: new FormControl('base'),
            login: new FormControl('Administrateur'),
            password: new FormControl('ThePassword'),
            ssl: new FormControl(false),
        });
    }

    deleteAnnuary(index: number) {
        this.annuaryConf.annuaries.splice(index, 1);
    }
}
