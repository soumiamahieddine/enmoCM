import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
    selector: 'app-prerequisite',
    templateUrl: './prerequisite.component.html',
    styleUrls: ['./prerequisite.component.scss']
})
export class PrerequisiteComponent implements OnInit {

    stepFormGroup: FormGroup;

    requiredPackages: any = {
        php: 'ok',
        rights: 'warning',
        unoconv: 'ko',
        netcat: 'ok'
    };

    packagesList: any = {
        general: [
            {
                label: 'phpVersion',
                description: 'Version de PHP (7.2, 7.3, ou 7.4) -> 7.2.31-1+ubuntu18.04.1+deb.sury.org+1'
            },
            {
                label: 'appRights',
                description: 'Droits de lecture et d\'écriture du répertoire racine de Maarch Courrier'
            },
            {
                label: 'appConversion',
                description: 'Outils de conversion de documents bureautiques soffice/unoconv installés'
            },
            {
                label: 'networkUtils',
                description: 'Utilitaire permettant d\'ouvrir des connexions réseau (netcat / nmap)'
            }
        ],
        libraries: [
            {
                label: 'pgsql',
                description: ''
            },
            {
                label: 'pdo_pgsql',
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
                label: 'XML-RPC',
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
                label: 'Imagick',
                description: ''
            },

        ],
        phpini: [
            {
                label: 'error_reporting',
                description: 'error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT '
            },
            {
                label: 'display_errors',
                description: 'display_errors = On'
            },
            {
                label: 'short_open_tags',
                description: 'short_open_tags = On'
            },
        ],
    };

    constructor(
        private _formBuilder: FormBuilder
    ) { }

    ngOnInit(): void {
        this.stepFormGroup = this._formBuilder.group({
            firstCtrl: ['', Validators.required]
        });

        // FOR TEST
        Object.keys(this.packagesList).forEach((group: any) => {
            this.packagesList[group] = this.packagesList[group].map(
                (item: any) => {
                    return {
                        ...item,
                        state: 'ok'
                    };
                }
            );
        });
        this.stepFormGroup.controls['firstCtrl'].setValue(this.checkStep());
        this.stepFormGroup.controls['firstCtrl'].markAsUntouched();
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
