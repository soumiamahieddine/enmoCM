import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/internal/operators/tap';
import { NotificationService } from '../../notification.service';
import { of } from 'rxjs/internal/observable/of';
import { catchError } from 'rxjs/internal/operators/catchError';

@Component({
    selector: 'app-prerequisite',
    templateUrl: './prerequisite.component.html',
    styleUrls: ['./prerequisite.component.scss']
})
export class PrerequisiteComponent implements OnInit {

    stepFormGroup: FormGroup;

    prerequisites: any = {};

    packagesList: any = {
        general: [
            {
                label: 'phpVersion',
                description: 'Version de PHP (7.2, 7.3, ou 7.4) -> 7.2.31-1+ubuntu18.04.1+deb.sury.org+1'
            },
            {
                label: 'writable',
                description: 'Droits de lecture et d\'écriture du répertoire racine de Maarch Courrier'
            },
            {
                label: 'unoconv',
                description: 'Outils de conversion de documents bureautiques soffice/unoconv installés'
            },
            {
                label: 'netcatOrNmap',
                description: 'Utilitaire permettant d\'ouvrir des connexions réseau (netcat / nmap)'
            }
        ],
        libraries: [
            {
                label: 'pgsql',
                description: ''
            },
            {
                label: 'fileinfo',
                description: ''
            },            {
                label: 'pdoPgsql',
                description: ''
            },
            {
                label: 'gd',
                description: ''
            },
            {
                label: 'imap',
                description: ''
            },
            {
                label: 'mbstring',
                description: ''
            },
            {
                label: 'xsl',
                description: ''
            },
            {
                label: 'gettext',
                description: ''
            },
            {
                label: 'xmlrpc',
                description: ''
            },
            {
                label: 'curl',
                description: ''
            },
            {
                label: 'zip',
                description: ''
            },
            {
                label: 'imagick',
                description: ''
            },

        ],
        phpini: [
            {
                label: 'errorReporting',
                description: 'error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT '
            },
            {
                label: 'displayErrors',
                description: 'display_errors = On'
            },
            {
                label: 'shortOpenTag',
                description: 'short_open_tags = On'
            },
        ],
    };

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        private _formBuilder: FormBuilder
    ) { }

    ngOnInit(): void {
        this.stepFormGroup = this._formBuilder.group({
            firstCtrl: ['', Validators.required]
        });

        // FOR TEST
        this.getStepData();
        this.stepFormGroup.controls['firstCtrl'].setValue(this.checkStep());
        this.stepFormGroup.controls['firstCtrl'].markAsUntouched();
    }

    getStepData() {
        this.http.get(`../rest/installer/prerequisites`).pipe(
            tap((data: any) => {
                this.prerequisites = data.prerequisites;
                Object.keys(this.packagesList).forEach(group => {
                    this.packagesList[group].forEach((item: any, key: number) => {
                        this.packagesList[group][key].state = this.prerequisites[this.packagesList[group][key].label] ? 'ok' : 'ko';
                    });
                });

            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    checkStep() {
        let state = 'success';
        Object.keys(this.packagesList).forEach((group: any) => {
            this.packagesList[group].forEach((item: any) => {
                if (item.state === 'ko') {
                    state = '';
                }
            });
        });
        return state;
    }

    isValidStep() {
        return this.stepFormGroup === undefined ? false : this.stepFormGroup.controls['firstCtrl'].value === 'success';
    }

    getFormGroup() {
        return this.stepFormGroup;
    }
}
