import { Component, OnInit} from '@angular/core';
import { Http } from '@angular/http';
import { Router, ActivatedRoute } from '@angular/router';
import 'rxjs/add/operator/map';
import { LANG } from './translate.component';

declare function $j(selector: any) : any;
declare function successNotification(message: string) : void;
declare function errorNotification(message: string) : void;

declare var angularGlobals : any;


@Component({
    templateUrl : angularGlobals["priority-administrationView"],
    styleUrls   : ['../../node_modules/bootstrap/dist/css/bootstrap.min.css']
})
export class PriorityAdministrationComponent implements OnInit {

    coreUrl         : string;
    id              : string;
    creationMode    : boolean;
    lang            : any       = LANG;
    loading         : boolean   = false;

    priority        : any       = {
        working_days    : false
    };


    constructor(public http: Http, private route: ActivatedRoute, private router: Router) {
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > <a onclick='location.hash = \"/administration/priorities\"' style='cursor: pointer'>Priorités</a>";
        }
    }

    ngOnInit(): void {
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.route.params.subscribe((params) => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;
                this.loading = false;
            } else {
                this.creationMode = false;
                this.id = params['id'];
                this.http.get(this.coreUrl + "rest/priorities/" + this.id)
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.priority = data.priority;

                        this.loading = false;
                    }, () => {
                        location.href = "index.php";
                    });
            }
        });
    }

    onSubmit(){
        if (this.creationMode) {
            this.http.post(this.coreUrl + "rest/priorities", this.priority)
                .map(res => res.json())
                .subscribe((data) => {
                    successNotification(data.success);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        } else {
            this.http.put(this.coreUrl + "rest/priorities/" + this.id, this.priority)
                .map(res => res.json())
                .subscribe((data) => {
                    successNotification(data.success);
                    this.router.navigate(["/administration/priorities"]);
                }, (err) => {
                    errorNotification(JSON.parse(err._body).errors);
                });
        }
    }

}