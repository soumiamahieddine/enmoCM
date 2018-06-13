import { ChangeDetectorRef, Component, OnInit, NgZone } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

declare function $j(selector: any): any;
declare var tinymce: any;
declare var angularGlobals: any;

@Component({
    templateUrl: "../../../../Views/template-administration.component.html",
    providers: [NotificationService]
})
export class TemplateAdministrationComponent implements OnInit {

    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    lang: any = LANG;
    coreUrl: string;
    creationMode: boolean;
    template: any = {};
    statuses: any[] = [];
    actionPagesList: any[] = [];
    categoriesList: any[] = [];
    keywordsList: any[] = [];
    defaultTemplatesList: any;
    attachmentTypesList: any;
    datasourcesList: any;
    jnlpValue: any = {};

    loading: boolean = false;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private zone: NgZone, private route: ActivatedRoute, private router: Router, private notify: NotificationService) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
        window['angularProfileComponent'] = {
            componentAfterUpload: (base64Content: any) => this.processAfterUpload(base64Content),
        };
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;

        this.route.params.subscribe(params => {
            if (typeof params['id'] == "undefined") {
                this.creationMode = true;

                this.http.get(this.coreUrl + 'rest/administration/templates/new')
                    .subscribe((data: any) => {
                        this.setInitialValue(data);
                        this.template.template_target = '';
                        this.template.template_type   = 'OFFICE';
                        this.loading                  = false;

                    });
            } else {
                this.creationMode = false;
                this.http.get(this.coreUrl + 'rest/templates/' + params['id'] + '/details')
                    .subscribe((data: any) => {
                        this.setInitialValue(data);
                        this.template = data.template;
                        this.loading  = false;
                        if(this.template.template_type=='HTML'){
                            this.initMce();
                        }
                    });
            }
            if(!this.template.template_attachment_type){
                this.template.template_attachment_type = 'all';
            }
        });
    }

    setInitialValue(data:any){
        this.defaultTemplatesList = data.templatesModels;
        this.attachmentTypesList  = data.attachmentTypes;
        this.datasourcesList      = data.datasources;
        setTimeout(() => {
            $j('#jstree').jstree({
                "checkbox": {
                    'three_state': 'down' // cascade selection
                },
                'core': {
                    'themes': {
                        'name': 'proton',
                        'responsive': true
                    },
                    'data': data.entities
                },
                "plugins": ["checkbox", "search", "sort"]
            });
            var to: any = false;
            $j('#jstree_search').keyup(function () {
                if (to) { clearTimeout(to); }
                to = setTimeout(function () {
                    var v = $j('#jstree_search').val();
                    $j('#jstree').jstree(true).search(v);
                }, 250);
            });
        }, 0);
    }

    initMce() {
        setTimeout(() => {
            tinymce.remove('textarea');
            //LOAD EDITOR TINYMCE for MAIL SIGN
            tinymce.baseURL = "../../node_modules/tinymce";
            tinymce.suffix = '.min';
            tinymce.init({
                selector: "textarea#templateHtml",
                statusbar: false,
                language: "fr_FR",
                language_url: "tools/tinymce/langs/fr_FR.js",
                height: "200",
                plugins: [
                    "textcolor"
                ],
                external_plugins: {
                    'bdesk_photo': "../../apps/maarch_entreprise/tools/tinymce/bdesk_photo/plugin.min.js"
                },
                menubar: false,
                toolbar: "undo | bold italic underline | alignleft aligncenter alignright | bdesk_photo | forecolor",
                theme_buttons1_add: "fontselect,fontsizeselect",
                theme_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
                theme_buttons2_add: "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
                theme_buttons3_add_before: "tablecontrols,separator",
                theme_buttons3_add: "separator,print,separator,ltr,rtl,separator,fullscreen,separator,insertlayer,moveforward,movebackward,absolut",
                theme_toolbar_align: "left",
                theme_advanced_toolbar_location: "top",
                theme_styles: "Header 1=header1;Header 2=header2;Header 3=header3;Table Row=tableRow1"
            });
        }, 20);
    }

    clickOnUploader(id: string) {
        $j('#' + id).click();
    }

    uploadFileTrigger(fileInput: any) {
        if (fileInput.target.files && fileInput.target.files[0]) {
            this.template.uploadedFile = {};
            this.template.uploadedFile.name = fileInput.target.files[0].name;
            this.template.uploadedFile.size = fileInput.target.files[0].size;
            this.template.uploadedFile.type = fileInput.target.files[0].type;
            if (this.template.uploadedFile.label == "") {
                this.template.uploadedFile.label = this.template.uploadedFile.name;
            }
            
            var reader = new FileReader();
            reader.readAsDataURL(fileInput.target.files[0]);
            
            reader.onload = function (value: any) {
                window['angularProfileComponent'].componentAfterUpload(value.target.result);
            };
        }
    }

    processAfterUpload(b64Content: any) {
        this.zone.run(() => this.resfreshUpload(b64Content));
    }

    resfreshUpload(b64Content: any) {
        this.template.uploadedFile.base64 = b64Content.replace(/^data:.*?;base64,/, "");
        this.template.template_style = null;
    }

    startJnlp() {
        if (this.creationMode) {
            this.jnlpValue.objectType = 'templateStyle';
            for(let element of this.defaultTemplatesList){
                if(this.template.template_style == element.fileExt + ': ' + element.fileName){
                    this.jnlpValue.objectId = element.filePath;
                }
            }
        } else {
            this.jnlpValue.objectType = 'template';
            this.jnlpValue.objectId   = this.template.template_id;
        }
        this.jnlpValue.table    = 'templates';
        this.jnlpValue.uniqueId = 0;
        this.jnlpValue.cookies = document.cookie;

        this.http.post(this.coreUrl + 'rest/jnlp', this.jnlpValue)
        .subscribe((data: any) => {
            this.template.userUniqueId = data.userUniqueId;
            this.template.uploadedFile = null;
            window.location.href       = this.coreUrl + 'rest/jnlp?fileName=' + data.generatedJnlp;
        }, (err) => {
            this.notify.error(err.error.errors);
        });        
    }

    duplicateTemplate()
    {
        let r = confirm(this.lang.confirmDuplicate);

        if (r) {
            this.http.post(this.coreUrl + 'rest/templates/' + this.template.template_id + '/duplicate', {'id': this.template.template_id})
            .subscribe((data:any) => {
                this.notify.success(this.lang.templateDuplicated);
                this.router.navigate(['/administration/templates/' + data.id]);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
        }
    }

    onSubmit() {
        this.template.entities = $j('#jstree').jstree(true).get_checked();
        if(this.template.template_target!='notifications'){
            this.template.template_datasource=='letterbox_attachment';
        }
        if(this.creationMode && this.template.template_style && !this.template.userUniqueId){
            alert(this.lang.editModelFirst);
            return;
        }
        if (this.creationMode) {
            this.http.post(this.coreUrl + 'rest/templates', this.template)
                .subscribe(() => {
                    this.router.navigate(['/administration/templates']);
                    this.notify.success(this.lang.templateAdded);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        } else {
            this.http.put(this.coreUrl + 'rest/templates/' + this.template.template_id, this.template)
                .subscribe(() => {
                    this.router.navigate(['/administration/templates']);
                    this.notify.success(this.lang.templateUpdated);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
        }
    }

    displayDatasources(datasource:any)
    {
        if(datasource.target=='notification' && this.template.template_target == 'notifications'){
            return true;
        } else if(datasource.target=='document' && this.template.template_target != 'notifications'){
            return true;
        }
        return false;
    }

    updateTemplateType()
    {
        if(this.template.template_target=='attachments'){
            this.template.template_type='OFFICE';
        } else if(this.template.template_target=='notifications' || this.template.template_target=='doctypes' || this.template.template_target=='sendmail'){
            this.template.template_type='HTML';
            this.initMce();
        } else if (this.template.template_target=='notes') {
            this.template.template_type='TXT';
        }
    }
}