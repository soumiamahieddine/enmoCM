import { Component, OnInit } from '@angular/core';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

declare function $j(selector: any) : any;
declare var Prototype : any;
declare function disablePrototypeJS(method: string, plugins: any) : any;


@Component({
  templateUrl: 'js/angular/app/Views/profile.html',
})
export class ProfileComponent implements OnInit {

    coreUrl                     : string;

    user                        : any       = {};
    passwordModel               : any       = {
        currentPassword         : "",
        newPassword             : "",
        reNewPassword           : "",
    };
    
    showPassword                : boolean   = false;
    loading                     : boolean   = false;


    constructor(public http: Http) {
    }

    prepareProfile() {
        $j('#inner_content').remove();
        $j('#menunav').hide();
        $j('#container').width("99%");

        if (Prototype.BrowserFeatures.ElementExtensions) {
            //FIX PROTOTYPE CONFLICT
            let pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover','tab'];
            disablePrototypeJS('show', pluginsToDisable);
            disablePrototypeJS('hide', pluginsToDisable);
        }
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

    displayPassword() {
        this.showPassword = !this.showPassword;
    }

    changePassword() {
        this.http.put(this.coreUrl + 'rest/user/password', this.passwordModel)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    alert(data.errors);
                } else {
                    this.showPassword = false;
                    this.passwordModel = {
                        currentPassword         : "",
                        newPassword             : "",
                        reNewPassword           : "",
                    };
                }
            });
    }

    onSubmit() {
        this.http.put(this.coreUrl + 'rest/user/profile', this.user)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.errors) {
                    alert(data.errors);
                }
            });
    }

}
