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
        //FIX PROTOTYPE CONFLICT
        if (Prototype.BrowserFeatures.ElementExtensions) {
            var disablePrototypeJS = function (method, pluginsToDisable) {
                    var handler = function (event) {
                        event.target[method] = undefined;
                        setTimeout(function () {
                            delete event.target[method];
                        }, 0);
                    };
                    pluginsToDisable.each(function (plugin) { 
                        jQuery(window).on(method + '.bs.' + plugin, handler);
                    });
                },
                pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover'];
            disablePrototypeJS('show', pluginsToDisable);
            disablePrototypeJS('hide', pluginsToDisable);
        }
        
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

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/user/' + this.user.user_id, this.user)
            .map(res => res.json())
            .subscribe((data) => {
            });
    }

}
