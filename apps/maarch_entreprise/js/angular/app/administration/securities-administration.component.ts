import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "../../../../Views/securities-administration.component.html",
    providers: [NotificationService]
})
export class SecuritiesAdministrationComponent implements OnInit {

    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl     : string;
    lang        : any = LANG;
    loading     : boolean = false;

    passwordRules: any = {
        minLength: { enabled: false, value: 0 },
        complexityUpper: { enabled: false, value: 0 },
        complexityNumber: { enabled: false, value: 0 },
        complexitySpecial: { enabled: false, value: 0 },
        renewal: { enabled: false, value: 0 },
        historyLastUse: { enabled: false, value: 0 },
        lockTime: { enabled: false, value: 0 },
        lockAttempts: { enabled: false, value: 0 },
    };

    passwordRulesList : any[] = [];
    passwordRulesListClone : any[] = [];


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.loading = true;
        this.coreUrl = angularGlobals.coreUrl;

        this.http.get(this.coreUrl + 'rest/passwordRules')
            .subscribe((data: any) => {
                this.passwordRulesList = data.rules;
                this.passwordRulesListClone = JSON.parse(JSON.stringify(this.passwordRulesList));
                data.rules.forEach((rule: any) => {
                    this.passwordRules[rule.label].value = rule.value;
                    this.passwordRules[rule.label].label = this.lang['password'+rule.label+'Required']

                    this.loading = false;
                });
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    cancelModification() {
        this.passwordRulesList = JSON.parse(JSON.stringify(this.passwordRulesListClone));
    }

    checkModif() {
        if (JSON.stringify(this.passwordRulesList) === JSON.stringify(this.passwordRulesListClone)) {
            return true 
        } else {
           return false;
        }
    }

    toggleRule(rule:any) {
        rule.enabled = !rule.enabled;
        if (rule.label == 'lockAttempts') {
            this.passwordRulesList.forEach((rule2: any) => {
                if (rule2.label == 'lockTime') {
                    rule2.enabled = !rule2.enabled;
                }
            });
        }
    }

    onSubmit() { 
        this.http.put(this.coreUrl + "rest/passwordRules", {rules:this.passwordRulesList})
            .subscribe((data: any) => {
                this.passwordRulesListClone = JSON.parse(JSON.stringify(this.passwordRulesList));
                this.notify.success(this.lang.passwordRulesUpdated);
            }, (err: any) => {
                this.notify.error(err.error.errors);
            });
    }
}
