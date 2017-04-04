import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: string) : any;


@Component({
  templateUrl: 'js/angular/app/Views/profile.html',
})
export class ProfileComponent implements OnInit {

    coreUrl                     : string;

    user                        : any = {};
    loading                     : boolean   = false;


    constructor(public http: Http) {
    }

    prepareProfile() {
        $j('#inner_content').remove();
        $j('#menunav').hide();
        $j('#container').width("99%");
    }

    ngOnInit(): void {
        this.prepareProfile();
        this.loading = true;

        this.http.get('index.php?display=true&page=initializeJsGlobalConfig')
            .map(res => res.json())
            .subscribe((data) => {
                this.coreUrl = data.coreurl;
                this.http.get(this.coreUrl + 'rest/user/profile')
                    .map(res => res.json())
                    .subscribe((data) => {
                        this.user = data;

                        this.loading = false;
                    });
            });
    }

}
