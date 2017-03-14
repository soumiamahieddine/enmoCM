import { Pipe, PipeTransform, Component, OnInit, NgZone } from '@angular/core';
import { Http } from '@angular/http';
import { DomSanitizer } from '@angular/platform-browser';
import { Router, ActivatedRoute } from '@angular/router';
import 'rxjs/add/operator/map';

declare function lockDocument(resId: number) : void;
declare function unlockDocument(resId: number) : void;
declare function valid_action_form(a1: string, a2: string, a3: string, a4: number, a5: string, a6: string, a7: string, a8: string, a9: boolean, a10: any) : void;
declare function $j(selector: string) : any;
declare function showAttachmentsForm(path: string) : void;
declare function modifyAttachmentsForm(path: string, width: string, height: string) : void;


@Pipe({ name: 'safeUrl' })
export class SafeUrlPipe implements PipeTransform {
    constructor(private sanitizer: DomSanitizer) {}
    transform(url: string) {
        return this.sanitizer.bypassSecurityTrustResourceUrl(url);
    }
}

@Component({
  templateUrl: 'js/angular/app/Views/signatureBook.html',
})
export class SignatureBookComponent implements OnInit {
    coreUrl                     : string;
    resId                       : number;
    basketId                    : string;

    signatureBook: any = {
        currentAction           : {},
        consigne                : "",
        documents               : [],
        attachments             : [],
        histories               : []
    };

    rightSelectedThumbnail      : number    = 0;
    leftSelectedThumbnail       : number    = 0;
    rightViewerLink             : string    = "";
    leftViewerLink              : string    = "";
    headerTab                   : number    = 1;
    showTopRightPanel           : boolean   = false;
    showTopLeftPanel            : boolean   = false;
    showResLeftPanel            : boolean   = true;
    showLeftPanel               : boolean   = true;
    showAttachmentEditionPanel  : boolean   = false;

    leftContentWidth            : string    = "39%";
    rightContentWidth           : string    = "39%";

    notesViewerLink             : string    = "";
    visaViewerLink              : string    = "";
    histViewerLink              : string    = "";



    constructor(public http: Http, private route: ActivatedRoute, private router: Router, private zone:NgZone) {
        window['angularSignatureBookComponent'] = {
            componentAfterAttach: (value: string) => this.processAfterAttach(value)
        };
    }

    ngOnInit(): void {
        this.prepareSignatureBook();
        this.route.params.subscribe(params => {
            this.resId      = +params['resId'];
            this.basketId   = params['basketId'];

            lockDocument(this.resId);
            setInterval(() => {lockDocument(this.resId)}, 50000);
            this.http.get('index.php?display=true&page=initializeJsGlobalConfig')
                .map(res => res.json())
                .subscribe((data) => {
                    this.coreUrl = data.coreurl;
                    this.http.get(this.coreUrl + 'rest/' + this.basketId + '/signatureBook/' + this.resId)
                        .map(res => res.json())
                        .subscribe((data) => {
                            this.signatureBook = data;

                            this.headerTab              = 1;
                            this.leftSelectedThumbnail  = 0;
                            this.rightSelectedThumbnail = 0;
                            this.leftViewerLink         = "";
                            this.rightViewerLink        = "";
                            this.showLeftPanel          = true;
                            this.showResLeftPanel       = true;
                            this.showTopLeftPanel       = false;
                            this.showTopRightPanel      = false;
                            this.showAttachmentEditionPanel  = false;
                            this.notesViewerLink = "index.php?display=true&module=notes&page=notes&identifier=" + this.resId + "&origin=document&coll_id=letterbox_coll&load&size=full";
                            this.visaViewerLink = "index.php?display=true&page=show_visa_tab&module=visa&resId=" + this.resId + "&collId=letterbox_coll&visaStep=true";
                            this.histViewerLink = "index.php?display=true&dir=indexing_searching&page=document_workflow_history&id=" + this.resId + "&coll_id=letterbox_coll&load&size=full";

                            if (this.signatureBook.documents[0]) {
                                this.leftViewerLink = this.signatureBook.documents[0].viewerLink;
                            }
                            if (this.signatureBook.attachments[0]) {
                                this.rightViewerLink = this.signatureBook.attachments[0].viewerLink;
                            }
                        });
                });
        });
    }

    prepareSignatureBook() {
        $j('#inner_content').remove();
        $j('#header').remove();
        $j('#viewBasketsTitle').remove();
        $j('#homePageWelcomeTitle').remove();
        $j('#footer').remove();
        $j('#container').width("98%");
    }

    changeSignatureBookLeftContent(id: number) {
        this.headerTab = id;
        this.showTopLeftPanel = false;
    }

    changeRightViewer(index: number) {
        if (index < 0) {
            this.showAttachmentEditionPanel = true;
        } else {
            this.rightViewerLink = this.signatureBook.attachments[index].viewerLink;
            this.showAttachmentEditionPanel = false;
        }
        this.rightSelectedThumbnail = index;
    }

    changeLeftViewer(index: number) {
        this.leftViewerLink = this.signatureBook.documents[index].viewerLink;
        this.leftSelectedThumbnail = index;
    }

    displayPanel(panel: string) {
        if (panel == "TOPRIGHT") {
            this.showTopRightPanel = !this.showTopRightPanel;
        } else if (panel == "TOPLEFT") {
            this.showTopLeftPanel = !this.showTopLeftPanel;
        } else if (panel == "LEFT") {
            this.showLeftPanel = !this.showLeftPanel;
            this.showResLeftPanel = false;
            if (!this.showLeftPanel) {
                this.rightContentWidth = "95%";
            } else {
                this.rightContentWidth = "45%";
                this.leftContentWidth = "45%";
            }
        } else if (panel == "RESLEFT") {
            this.showResLeftPanel = !this.showResLeftPanel;
            if (!this.showResLeftPanel) {
                this.rightContentWidth = "45%";
                this.leftContentWidth = "45%";
            } else {
                this.rightContentWidth = "39%";
                this.leftContentWidth = "39%";
            }
        }
    }

    prepareSignFile(attachment: any) {
        if (attachment.res_id == 0) {
            this.signatureBookSignFile(attachment.res_id_version, 1);
        } else if (attachment.res_id_version == 0) {
            this.signatureBookSignFile(attachment.res_id, 0);
        }
    }

    signatureBookSignFile(resId: number, type: number) {
        var path = '';

        if (type == 0) {
            path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&resIdMaster=' + this.resId + '&id=' + resId;
        } else if (type == 1) {
            path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isVersion&resIdMaster=' + this.resId + '&id=' + resId;
        } else if (type == 2) {
            path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isOutgoing&resIdMaster=' + this.resId + '&id=' + resId;
        }

        this.http.get(path)
            .map(res => res.json())
            .subscribe((data) => {
                if (data.status == 0) {
                    this.rightViewerLink = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=" + this.resId + "&id=" + data.new_id;
                    this.signatureBook.attachments[this.rightSelectedThumbnail].viewerLink = this.rightViewerLink;
                    this.signatureBook.attachments[this.rightSelectedThumbnail].status = 'SIGN';
                } else {
                    alert(data.error);
                }
            });

    }

    unsignFile(attachment: any) {
        var collId: string;
        var resId: number;

        if (attachment.res_id == 0) {
            resId = attachment.res_id_version;
            collId = "res_version_attachments";
        } else if (attachment.res_id_version == 0) {
            resId = attachment.res_id;
            collId = "res_attachments";
        }

        this.http.put(this.coreUrl + 'rest/' + collId + '/' + resId + '/unsign', {}, {})
            .map(res => res.json())
            .subscribe((data) => {
                if (data.status == "OK") {
                    this.rightViewerLink = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=" + this.resId + "&id=" + resId;
                    this.signatureBook.attachments[this.rightSelectedThumbnail].viewerLink = this.rightViewerLink;
                    this.signatureBook.attachments[this.rightSelectedThumbnail].status = 'A_TRA';
                } else {
                    alert(data.error);
                }
            });

    }

    backToBasket() {
        location.hash = "";
        location.reload();
    }

    changeLocation(resId: number) {
        let path = "/" + this.basketId + "/signatureBook/" + resId;
        this.router.navigate([path]);
    }

    validForm() {
        if ($j("#signatureBookActions option:selected")[0].value != "") {
            unlockDocument(this.resId);

            valid_action_form(
                'empty',
                'index.php?display=true&page=manage_action&module=core',
                this.signatureBook.currentAction.id,
                this.resId,
                'res_letterbox',
                'null',
                'letterbox_coll',
                'page',
                false,
                [$j("#signatureBookActions option:selected")[0].value]
            );
        }
    }

    refreshAttachments(mode: string) {
        this.http.get(this.coreUrl + 'rest/' + 'signatureBook/' + this.resId + '/attachments')
            .map(res => res.json())
            .subscribe((data) => {
                this.signatureBook.attachments = data;
                if (mode == "add") {
                    this.changeRightViewer(this.signatureBook.attachments.length - 1);
                } else if (mode == "del") {
                    this.changeRightViewer(0);
                }
            });
    }

    processAfterAttach(mode: string) {
        this.zone.run(() => this.refreshAttachments(mode));
    }

    addAttachmentIframe() {
        showAttachmentsForm('index.php?display=true&module=attachments&page=attachments_content&docId=' + this.resId);
    }

    editAttachmentIframe(attachment: any) {
        var resId: number;
        if (attachment.res_id == 0) {
            resId = attachment.res_id_version;
        } else if (attachment.res_id_version == 0) {
            resId = attachment.res_id;
        }

        modifyAttachmentsForm('index.php?display=true&module=attachments&page=attachments_content&id=' + resId + '&relation=' + attachment.relation + '&docId=' + this.resId, '98%', 'auto');
    }

    delAttachment(attachment: any) {
        let r = confirm('Voulez-vous vraiment supprimer la pièce jointe ?');
        if (r) {
            var resId: number;
            if (attachment.res_id == 0) {
                resId = attachment.res_id_version;
            } else if (attachment.res_id_version == 0) {
                resId = attachment.res_id;
            }

            this.http.get('index.php?display=true&module=attachments&page=del_attachment&id=' + resId + '&relation=' + attachment.relation + '&rest=true')
                .subscribe(() => {
                    this.refreshAttachments('del');
                });
        }
    }
}
