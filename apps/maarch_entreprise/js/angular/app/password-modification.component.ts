import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { FormBuilder, FormGroup, Validators, ValidationErrors, AbstractControl, ValidatorFn } from '@angular/forms';

declare function $j(selector: any): any;

declare var tinymce: any;
declare var angularGlobals: any;


@Component({
    templateUrl: "../../../Views/password-modification.component.html",
    providers: [NotificationService]
})
export class PasswordModificationComponent implements OnInit {

    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;

    coreUrl: string;
    ruleText: string = '';
    OtherRuleText: string;
    lang: any = LANG;
    loading: boolean = false;
    user: any = {};
    hidePassword: Boolean = true;

    passLength: any = false;
    passwordRules: any = {
        minLength: { enabled: false, value: 0 },
        complexityUpper: { enabled: false, value: 0 },
        complexityNumber: { enabled: false, value: 0 },
        complexitySpecial: { enabled: false, value: 0 },
        renewal: { enabled: false, value: 0 },
    };

    passwordModel: any = {
        currentPassword: "",
        newPassword: "",
        reNewPassword: "",
    };
    arrValidator: any[] = [];
    validPassword: Boolean = false;
    isLinear = false;
    firstFormGroup: FormGroup;
    secondFormGroup: FormGroup;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, private _formBuilder: FormBuilder) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
        this.user = angularGlobals.user;
    }

    regexValidator(regex: RegExp, error: ValidationErrors): ValidatorFn {
        return (control: AbstractControl): { [key: string]: any } => {
            if (!control.value) {
                return null;
            }
            const valid = regex.test(control.value);
            return valid ? null : error;
        };
    }

    prepare() {
        $j("link[href='merged_css.php']").remove();
        //$j('#header').remove();
        $j('#footer').remove();
        $j('#inner_content').remove();
        $j('#inner_content_contact').parent('div').remove();
        $j('#inner_content_contact').remove();
        $j('#menunav').hide();
        $j('#divList').remove();
        $j('#magicContactsTable').remove();
        $j('#manageBasketsOrderTable').remove();
        $j('#controlParamTechnicTable').remove();
        $j('#container').width("99%");
        if ($j('#content h1')[0] && $j('#content h1')[0] != $j('my-app h1')[0]) {
            $j('#content h1')[0].remove();
        }
    }

    ngOnInit(): void {
        this.prepare();
        this.coreUrl = angularGlobals.coreUrl;
        this.http.get(this.coreUrl + 'rest/passwordRules')
            .subscribe((data: any) => {
                let ruleTextArr: String[] = [];
                data.rules.forEach((rule: any) => {


                    if (rule.label == 'minLength') {
                        this.passwordRules.minLength.enabled = rule.enabled;
                        this.passwordRules.minLength.value = rule.value;
                        ruleTextArr.push(rule.value + ' ' + this.lang['password' + rule.label]);

                    } else if (rule.label == 'complexityUpper') {
                        this.passwordRules.complexityUpper.enabled = rule.enabled;
                        this.passwordRules.complexityUpper.value = rule.value;
                        ruleTextArr.push(this.lang['password' + rule.label]);

                    } else if (rule.label == 'complexityNumber') {
                        this.passwordRules.complexityNumber.enabled = rule.enabled;
                        this.passwordRules.complexityNumber.value = rule.value;
                        ruleTextArr.push(this.lang['password' + rule.label]);

                    } else if (rule.label == 'complexitySpecial') {
                        this.passwordRules.complexitySpecial.enabled = rule.enabled;
                        this.passwordRules.complexitySpecial.value = rule.value;
                        ruleTextArr.push(this.lang['password' + rule.label]);

                    } else if (rule.label == 'renewal') {
                        this.passwordRules.renewal.enabled = rule.enabled;
                        this.passwordRules.renewal.value = rule.value;
                        this.OtherRuleText = this.lang['password' + rule.label] + ' <b>' + rule.value + ' ' + this.lang.days + '</b>. ' + this.lang['password2' + rule.label];
                    }

                });
                this.ruleText = ruleTextArr.join(', ');
            }, (err) => {
                this.notify.error(err.error.errors);
            });

        this.firstFormGroup = this._formBuilder.group({
            firstCtrl: [
                '',
                Validators.compose([Validators.minLength(6), this.regexValidator(new RegExp('[A-Z]'), { 'complexityUpper': '' }), this.regexValidator(new RegExp('[0-9]'), { 'complexityNumber': '' }), this.regexValidator(new RegExp('[^A-Za-z0-9]'), { 'complexitySpecial': '' })])
            ]
        });
        //console.log(this.passwordRules);
    }

    getErrorMessage() {
        //console.log(this.firstFormGroup.controls['firstCtrl'].errors);
        if (this.firstFormGroup.controls['firstCtrl'].hasError('required')) {
            return 'Champ requis !';

        } else if (this.firstFormGroup.controls['firstCtrl'].hasError('minlength') && this.passwordRules.minLength.enabled) {
            return this.passwordRules.minLength.value + ' ' + this.lang.passwordminLength + ' !';

        } else if (this.firstFormGroup.controls['firstCtrl'].errors != null && this.firstFormGroup.controls['firstCtrl'].errors.complexityUpper !== undefined && this.passwordRules.complexityUpper.enabled) {
            return this.lang.passwordcomplexityUpper + ' !';

        } else if (this.firstFormGroup.controls['firstCtrl'].errors != null && this.firstFormGroup.controls['firstCtrl'].errors.complexityNumber !== undefined && this.passwordRules.complexityNumber.enabled) {
            return this.lang.passwordcomplexityNumber + ' !';

        } else if (this.firstFormGroup.controls['firstCtrl'].errors != null && this.firstFormGroup.controls['firstCtrl'].errors.complexitySpecial !== undefined && this.passwordRules.complexitySpecial.enabled) {
            return this.lang.passwordcomplexitySpecial + ' !';

        } else {
            this.validPassword = true;
            return '';
        }
    }
}
