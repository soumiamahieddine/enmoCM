import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ValidatorFn } from '@angular/forms';
import { LANG } from '../../translate.component';
import { StepAction } from '../types';
import { DomSanitizer } from '@angular/platform-browser';
import { NotificationService } from '../../../service/notification/notification.service';
import { environment } from '../../../environments/environment';
import { ScanPipe } from 'ngx-pipes';
import { debounceTime, filter, tap } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';

declare var tinymce: any;

@Component({
    selector: 'app-customization',
    templateUrl: './customization.component.html',
    styleUrls: ['./customization.component.scss'],
    providers: [ScanPipe]
})
export class CustomizationComponent implements OnInit {
    lang: any = LANG;
    stepFormGroup: FormGroup;

    backgroundList: any[] = [];

    constructor(
        private _formBuilder: FormBuilder,
        private notify: NotificationService,
        private sanitizer: DomSanitizer,
        private scanPipe: ScanPipe,
        public http: HttpClient,
    ) {
        const valIdentifier: ValidatorFn[] = [Validators.pattern(/^[a-zA-Z0-9_\-]*$/), Validators.required];

        this.stepFormGroup = this._formBuilder.group({
            firstCtrl: ['success', Validators.required],
            customId: ['cs_maarchcourrier', valIdentifier],
            appName: [`Maarch Courrier ${environment.VERSION.split('.')[0] + '.' + environment.VERSION.split('.')[1]}`, Validators.required],
            loginMessage: [`<span style="color:#24b0ed"><strong>Découvrez votre application via</strong></span>&nbsp;<a title="le guide de visite" href="https://docs.maarch.org/gitbook/html/MaarchCourrier/${environment.VERSION.split('.')[0] + '.' + environment.VERSION.split('.')[1]}/guu/home.html" target="_blank"><span style="color:#f99830;"><strong>le guide de visite en ligne</strong></span></a>`],
            homeMessage: ['<p>D&eacute;couvrez <strong>Maarch Courrier 20.10</strong> avec <a title="notre guide de visite" href="https://docs.maarch.org/" target="_blank"><span style="color:#f99830;"><strong>notre guide de visite en ligne</strong></span></a>.</p>'],
            bodyLoginBackground: ['bodylogin.jpg'],
            uploadedLogo: ['../rest/images?image=logo'],
        });
        this.backgroundList = Array.from({ length: 16 }).map((_, i) => {
            return {
                filename: `${i + 1}.jpg`,
                url: `assets/${i + 1}.jpg`,
            };
        });
    }

    ngOnInit(): void {
        this.stepFormGroup.controls['customId'].valueChanges.pipe(
            tap(() => {
                if (this.stepFormGroup.controls['customId'].errors !== null) {
                    const errors = Object.keys(this.stepFormGroup.controls['customId']?.errors).length > 0 ? { ...this.stepFormGroup.controls['customId'].errors} : null;
                    this.stepFormGroup.controls['customId'].setErrors(errors);
                }
            }),
            debounceTime(500),
            filter((value: any) => value.length > 2),
            tap(() => {
                this.notify.error('TO DO WAIT BACK ../rest/installer/customExist');
                // this.checkCustomExist();
            }),
            ).subscribe();
    }

    initStep() {
        this.initMce();
        // this.checkCustomExist();
        return false;
    }

    checkCustomExist() {
        this.http.get('../rest/installer/customExist', { params: { 'search': this.stepFormGroup.controls['customId'].value } }).pipe(
            tap((response: any) => {
                if (!response) {
                    // this.stepFormGroup.controls['customId'].setErrors({ ...this.stepFormGroup.controls['customId'].errors, customExist: true });
                    // this.stepFormGroup.controls['customId'].markAsTouched();
                } else {
                    const error = this.stepFormGroup.controls['customId'].errors;
                    delete error.customExist;
                    // this.stepFormGroup.controls['customId'].setErrors(error);
                    // this.stepFormGroup.controls['customId'].markAsTouched();
                }
            })
        ).subscribe();
    }

    isValidStep() {
        return this.stepFormGroup === undefined ? false : this.stepFormGroup.valid;
    }


    getFormGroup() {
        return this.stepFormGroup;
    }

    initMce() {
        tinymce.init({
            selector: 'textarea',
            base_url: '../node_modules/tinymce/',
            height: '150',
            suffix: '.min',
            language: LANG.langISO.replace('-', '_'),
            language_url: `../node_modules/tinymce-i18n/langs/${LANG.langISO.replace('-', '_')}.js`,
            menubar: false,
            statusbar: false,
            plugins: [
                'autolink'
            ],
            external_plugins: {
                'maarch_b64image': '../../src/frontend/plugins/tinymce/maarch_b64image/plugin.min.js'
            },
            toolbar_sticky: true,
            toolbar_drawer: 'floating',
            toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline strikethrough forecolor | maarch_b64image | \
        alignleft aligncenter alignright alignjustify \
        bullist numlist outdent indent | removeformat'
        });
    }

    getInfoToInstall(): StepAction[] {
        return [
            {
                body: {
                    customId: this.stepFormGroup.controls['customId'].value,
                    applicationName: this.stepFormGroup.controls['appName'].value,
                },
                description: this.lang.stepInstanceActionDesc,
                route: '../rest/installer/custom',
                installPriority: 1
            },
            {
                body: {
                    loginMessage: this.stepFormGroup.controls['loginMessage'].value,
                    homeMessage: this.stepFormGroup.controls['homeMessage'].value,
                    bodyLoginBackground: this.stepFormGroup.controls['bodyLoginBackground'].value,
                    logo: this.stepFormGroup.controls['uploadedLogo'].value,
                },
                description: this.lang.stepCustomizationActionDesc,
                route: '../rest/installer/customization',
                installPriority: 3
            }
        ];
    }

    uploadTrigger(fileInput: any, mode: string) {
        if (fileInput.target.files && fileInput.target.files[0]) {
            const allowedExtension = mode !== 'logo' ? ['image/jpg'] : ['image/svg+xml'];
            if (allowedExtension.indexOf(fileInput.target.files[0].type) !== -1) {
                const reader = new FileReader();

                reader.readAsDataURL(fileInput.target.files[0]);
                reader.onload = (value: any) => {
                    if (mode === 'logo') {
                        this.stepFormGroup.controls['uploadedLogo'].setValue(value.target.result);
                    } else {
                        this.backgroundList.push({
                            filename: value.target.result,
                            url: value.target.result,
                        });
                        this.stepFormGroup.controls['bodyLoginBackground'].setValue(value.target.result);
                    }
                };
            } else {
                this.notify.error(this.scanPipe.transform(this.lang.onlyExtensionsAllowed, [allowedExtension.join(', ')]));
            }
        }
    }

    logoURL() {
        return this.sanitizer.bypassSecurityTrustUrl(this.stepFormGroup.controls['uploadedLogo'].value);
    }
}
