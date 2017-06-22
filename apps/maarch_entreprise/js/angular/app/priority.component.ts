import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
import { Router, ActivatedRoute } from '@angular/router';

declare function $j(selector: any) : any;

declare var angularGlobals : any;
@Component({
    templateUrl : angularGlobals.parameterView,
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css','css/parameter.component.css']
})

export class PriorityComponent implements OnInit {
    coreUrl     :string;

    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    ngOnInit(): void{
        this.prepareParameter();
    }

    prepareParameter() {
        $j('#inner_content').remove();
    }

}