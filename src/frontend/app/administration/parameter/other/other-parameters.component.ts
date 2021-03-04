import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { KeyValue } from '@angular/common';
import { FormControl } from '@angular/forms';
import { debounceTime, tap } from 'rxjs/operators';

@Component({
    selector: 'app-other-parameters',
    templateUrl: './other-parameters.component.html',
    styleUrls: ['./other-parameters.component.scss'],
})
export class OtherParametersComponent implements OnInit {

    editorsConf: any = {
        java: {},
        onlyoffice : {
            server_ssl: new FormControl(false),
            server_uri : new FormControl('192.168.0.11'),
            server_port: new FormControl(8765),
            server_token : new FormControl('')
        },
        collaboraonline : {
            server_ssl: new FormControl(false),
            server_uri : new FormControl('192.168.0.11'),
            server_port: new FormControl(9980),
            server_token : new FormControl(''),
            editor_language: new FormControl('fr-FR')
        }
    };

    editorsEnabled = ['java', 'onlyoffice'];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
    ) { }

    ngOnInit() {
        Object.keys(this.editorsConf).forEach(editorId => {
            Object.keys(this.editorsConf[editorId]).forEach((elementId: any) => {
                this.editorsConf[editorId][elementId].valueChanges
                    .pipe(
                        debounceTime(300),
                        tap((value: any) => {
                            console.log(value);
                            this.saveConfEditor();
                        }),
                    ).subscribe();
            });
        });
    }

    getInputType(value: any) {
        return typeof value;
    }

    originalOrder = (a: KeyValue<string, any>, b: KeyValue<string, any>): number => {
        return 0;
    }

    addEditor(id: string) {
        this.editorsEnabled.push(id);
    }

    removeEditor(index: number) {
        this.editorsEnabled.splice(index, 1);
    }

    getAvailableEditors() {
        const allEditors = Object.keys(this.editorsConf);
        const availableEditors = allEditors.filter(editor => this.editorsEnabled.indexOf(editor) === -1);
        return availableEditors;
    }

    allEditorsEnabled() {
        return Object.keys(this.editorsConf).length === this.editorsEnabled.length;
    }

    saveConfEditor() {
        console.log(this.formatEditorsConfig());

        /*this.http.put(`../rest/configurations/documentEditor`, this.formatEditorsConfig()).pipe(
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })*/
    }

    formatEditorsConfig() {
        const obj: any = {};
        Object.keys(this.editorsConf).forEach(id => {
            if (this.editorsEnabled.indexOf(id) > -1) {
                obj[id] = {};
                Object.keys(this.editorsConf[id]).forEach(elemId => {
                    obj[id][elemId] = this.editorsConf[id][elemId].value;
                });
            }
        });
        return obj;
    }
}
