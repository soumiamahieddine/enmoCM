"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
var core_1 = require('@angular/core');
var http_1 = require('@angular/http');
var platform_browser_1 = require('@angular/platform-browser');
var router_1 = require('@angular/router');
require('rxjs/add/operator/map');
var SafeUrlPipe = (function () {
    function SafeUrlPipe(sanitizer) {
        this.sanitizer = sanitizer;
    }
    SafeUrlPipe.prototype.transform = function (url) {
        return this.sanitizer.bypassSecurityTrustResourceUrl(url);
    };
    SafeUrlPipe = __decorate([
        core_1.Pipe({ name: 'safeUrl' }), 
        __metadata('design:paramtypes', [platform_browser_1.DomSanitizer])
    ], SafeUrlPipe);
    return SafeUrlPipe;
}());
exports.SafeUrlPipe = SafeUrlPipe;
var SignatureBookComponent = (function () {
    function SignatureBookComponent(http, route, router, zone) {
        var _this = this;
        this.http = http;
        this.route = route;
        this.router = router;
        this.zone = zone;
        this.signatureBook = {
            currentAction: {},
            consigne: "",
            documents: [],
            attachments: [],
            //histories               : [],
            resList: []
        };
        this.rightSelectedThumbnail = 0;
        this.leftSelectedThumbnail = 0;
        this.rightViewerLink = "";
        this.leftViewerLink = "";
        this.headerTab = 1;
        this.showTopRightPanel = false;
        this.showTopLeftPanel = false;
        this.showResLeftPanel = true;
        this.showLeftPanel = true;
        this.showRightPanel = true;
        this.showAttachmentEditionPanel = false;
        this.loading = false;
        this.loadingSign = false;
        this.leftContentWidth = "44%";
        this.rightContentWidth = "44%";
        this.notesViewerLink = "";
        this.visaViewerLink = "";
        this.histViewerLink = "";
        this.linksViewerLink = "";
        window['angularSignatureBookComponent'] = {
            componentAfterAttach: function (value) { return _this.processAfterAttach(value); },
            componentAfterAction: function () { return _this.processAfterAction(); },
            componentAfterNotes: function () { return _this.processAfterNotes(); }
        };
    }
    SignatureBookComponent.prototype.prepareSignatureBook = function () {
        $j('#inner_content').remove();
        $j('#header').remove();
        $j('#viewBasketsTitle').remove();
        $j('#homePageWelcomeTitle').remove();
        $j('#footer').remove();
        $j('#container').width("99%");
    };
    SignatureBookComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareSignatureBook();
        this.loading = true;
        this.route.params.subscribe(function (params) {
            _this.resId = +params['resId'];
            _this.basketId = params['basketId'];
            lockDocument(_this.resId);
            setInterval(function () { lockDocument(_this.resId); }, 50000);
            _this.http.get('index.php?display=true&page=initializeJsGlobalConfig')
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.coreUrl = data.coreurl;
                _this.http.get(_this.coreUrl + 'rest/' + _this.basketId + '/signatureBook/' + _this.resId)
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    if (data.error) {
                        location.hash = "";
                        location.search = "";
                        return;
                    }
                    _this.signatureBook = data;
                    _this.headerTab = 1;
                    _this.leftSelectedThumbnail = 0;
                    _this.rightSelectedThumbnail = 0;
                    _this.leftViewerLink = "";
                    _this.rightViewerLink = "";
                    _this.showLeftPanel = true;
                    _this.showRightPanel = true;
                    _this.showResLeftPanel = true;
                    _this.showTopLeftPanel = false;
                    _this.showTopRightPanel = false;
                    _this.showAttachmentEditionPanel = false;
                    _this.notesViewerLink = "index.php?display=true&module=notes&page=notes&identifier=" + _this.resId + "&origin=document&coll_id=letterbox_coll&load&size=full";
                    _this.visaViewerLink = "index.php?display=true&page=show_visa_tab&module=visa&resId=" + _this.resId + "&collId=letterbox_coll&visaStep=true";
                    _this.histViewerLink = "index.php?display=true&dir=indexing_searching&page=document_workflow_history&id=" + _this.resId + "&coll_id=letterbox_coll&load&size=full";
                    _this.linksViewerLink = "index.php?display=true&page=show_links_tab&id=" + _this.resId;
                    _this.leftContentWidth = "44%";
                    _this.rightContentWidth = "44%";
                    if (_this.signatureBook.documents[0]) {
                        _this.leftViewerLink = _this.signatureBook.documents[0].viewerLink;
                    }
                    if (_this.signatureBook.attachments[0]) {
                        _this.rightViewerLink = _this.signatureBook.attachments[0].viewerLink;
                    }
                    if (_this.loading) {
                        _this.displayPanel("RESLEFT");
                    }
                    _this.loading = false;
                    setTimeout(function () {
                        $j("#resListContent").niceScroll({ touchbehavior: false, cursorcolor: "#666", cursoropacitymax: 0.6, cursorwidth: 4 });
                        $j("#rightPanelContent").niceScroll({ touchbehavior: false, cursorcolor: "#666", cursoropacitymax: 0.6, cursorwidth: 4 });
                        $j("#resListContent").scrollTop(0);
                        $j("#resListContent").scrollTop($j(".resListContentFrameSelected").offset().top - 42);
                        $j("#obsVersion").tooltipster({
                            interactive: true
                        });
                    }, 0);
                });
            });
        });
    };
    SignatureBookComponent.prototype.ngOnDestroy = function () {
        delete window['angularSignatureBookComponent'];
    };
    SignatureBookComponent.prototype.processAfterAttach = function (mode) {
        var _this = this;
        this.zone.run(function () { return _this.refreshAttachments(mode); });
    };
    SignatureBookComponent.prototype.processAfterNotes = function () {
        var _this = this;
        this.zone.run(function () { return _this.refreshNotes(); });
    };
    SignatureBookComponent.prototype.processAfterAction = function () {
        var _this = this;
        var idToGo = -1;
        var c = this.signatureBook.resList.length;
        for (var i = 0; i < c; i++) {
            if (this.signatureBook.resList[i].res_id == this.resId) {
                if (this.signatureBook.resList[i + 1]) {
                    idToGo = this.signatureBook.resList[i + 1].res_id;
                }
                else if (i > 0) {
                    idToGo = this.signatureBook.resList[i - 1].res_id;
                }
            }
        }
        if (idToGo >= 0) {
            this.zone.run(function () { return _this.changeLocation(idToGo, "action"); });
        }
        else {
            this.zone.run(function () { return _this.backToBasket(); });
        }
    };
    SignatureBookComponent.prototype.changeSignatureBookLeftContent = function (id) {
        this.headerTab = id;
        this.showTopLeftPanel = false;
    };
    SignatureBookComponent.prototype.changeRightViewer = function (index) {
        if (index < 0) {
            this.showAttachmentEditionPanel = true;
        }
        else {
            this.rightViewerLink = this.signatureBook.attachments[index].viewerLink;
            this.showAttachmentEditionPanel = false;
        }
        this.rightSelectedThumbnail = index;
    };
    SignatureBookComponent.prototype.changeLeftViewer = function (index) {
        this.leftViewerLink = this.signatureBook.documents[index].viewerLink;
        this.leftSelectedThumbnail = index;
    };
    SignatureBookComponent.prototype.displayPanel = function (panel) {
        if (panel == "TOPRIGHT") {
            this.showTopRightPanel = !this.showTopRightPanel;
        }
        else if (panel == "TOPLEFT") {
            this.showTopLeftPanel = !this.showTopLeftPanel;
        }
        else if (panel == "LEFT") {
            this.showLeftPanel = !this.showLeftPanel;
            this.showResLeftPanel = false;
            if (!this.showLeftPanel) {
                this.rightContentWidth = "96%";
                $j("#hideLeftContent").css('background', 'none');
            }
            else {
                this.rightContentWidth = "48%";
                this.leftContentWidth = "48%";
                $j("#hideLeftContent").css('background', '#CEE9F1');
            }
        }
        else if (panel == "RESLEFT") {
            this.showResLeftPanel = !this.showResLeftPanel;
            if (!this.showResLeftPanel) {
                this.rightContentWidth = "48%";
                this.leftContentWidth = "48%";
            }
            else {
                this.rightContentWidth = "44%";
                this.leftContentWidth = "44%";
            }
        }
        else if (panel == "MIDDLE") {
            this.showRightPanel = !this.showRightPanel;
            this.showResLeftPanel = false;
            if (!this.showRightPanel) {
                this.leftContentWidth = "96%";
                $j("#contentLeft").css('border-right', 'none');
            }
            else {
                this.rightContentWidth = "48%";
                this.leftContentWidth = "48%";
                $j("#contentLeft").css('border-right', 'solid 1px');
            }
        }
    };
    SignatureBookComponent.prototype.refreshAttachments = function (mode) {
        var _this = this;
        if (mode == "rightContent") {
            this.http.get(this.coreUrl + 'rest/signatureBook/' + this.resId + '/incomingMailAttachments')
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.signatureBook.documents = data;
            });
        }
        else {
            this.http.get(this.coreUrl + 'rest/signatureBook/' + this.resId + '/attachments')
                .map(function (res) { return res.json(); })
                .subscribe(function (data) {
                var i = 0;
                if (mode == "add") {
                    var found = false;
                    data.forEach(function (elem, index) {
                        if (!found && (!_this.signatureBook.attachments[index] || elem.res_id != _this.signatureBook.attachments[index].res_id)) {
                            i = index;
                            found = true;
                        }
                    });
                }
                else if (mode == "edit") {
                    var id = _this.signatureBook.attachments[_this.rightSelectedThumbnail].res_id;
                    data.forEach(function (elem, index) {
                        if (elem.res_id == id) {
                            i = index;
                        }
                    });
                }
                _this.signatureBook.attachments = data;
                if (mode == "add" || mode == "edit") {
                    _this.changeRightViewer(i);
                }
                else if (mode == "del") {
                    _this.changeRightViewer(0);
                }
            });
        }
    };
    SignatureBookComponent.prototype.addAttachmentIframe = function () {
        showAttachmentsForm('index.php?display=true&module=attachments&page=attachments_content&docId=' + this.resId);
    };
    SignatureBookComponent.prototype.editAttachmentIframe = function (attachment) {
        if (attachment.canModify && attachment.status != "SIGN") {
            var resId;
            if (attachment.res_id == 0) {
                resId = attachment.res_id_version;
            }
            else if (attachment.res_id_version == 0) {
                resId = attachment.res_id;
            }
            modifyAttachmentsForm('index.php?display=true&module=attachments&page=attachments_content&id=' + resId + '&relation=' + attachment.relation + '&docId=' + this.resId, '98%', 'auto');
        }
    };
    SignatureBookComponent.prototype.delAttachment = function (attachment) {
        var _this = this;
        if (attachment.canDelete) {
            var r = confirm('Voulez-vous vraiment supprimer la pièce jointe ?');
            if (r) {
                var resId;
                if (attachment.res_id == 0) {
                    resId = attachment.res_id_version;
                }
                else if (attachment.res_id_version == 0) {
                    resId = attachment.res_id;
                }
                this.http.get('index.php?display=true&module=attachments&page=del_attachment&id=' + resId + '&relation=' + attachment.relation + '&rest=true')
                    .subscribe(function () {
                    _this.refreshAttachments('del');
                });
            }
        }
    };
    SignatureBookComponent.prototype.refreshNotes = function () {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/res/' + this.resId + '/notes/count')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            _this.signatureBook.nbNotes = data;
        });
    };
    SignatureBookComponent.prototype.prepareSignFile = function (attachment) {
        if (!this.loadingSign) {
            if (attachment.res_id == 0) {
                this.signatureBookSignFile(attachment.res_id_version, 1);
            }
            else if (attachment.res_id_version == 0) {
                this.signatureBookSignFile(attachment.res_id, 0);
            }
        }
    };
    SignatureBookComponent.prototype.signatureBookSignFile = function (resId, type) {
        var _this = this;
        this.loadingSign = true;
        var path = '';
        if (type == 0) {
            path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&resIdMaster=' + this.resId + '&id=' + resId;
        }
        else if (type == 1) {
            path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isVersion&resIdMaster=' + this.resId + '&id=' + resId;
        }
        else if (type == 2) {
            path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isOutgoing&resIdMaster=' + this.resId + '&id=' + resId;
        }
        this.http.get(path)
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.status == 0) {
                _this.rightViewerLink = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=" + _this.resId + "&id=" + data.new_id;
                _this.signatureBook.attachments[_this.rightSelectedThumbnail].viewerLink = _this.rightViewerLink;
                _this.signatureBook.attachments[_this.rightSelectedThumbnail].status = 'SIGN';
                _this.signatureBook.attachments[_this.rightSelectedThumbnail].idToDl = data.new_id;
                var allSigned = true;
                _this.signatureBook.attachments.forEach(function (value) {
                    if (value.sign && value.status != 'SIGN') {
                        allSigned = false;
                    }
                });
                _this.signatureBook.resList[_this.signatureBook.resListIndex].allSigned = allSigned;
            }
            else {
                alert(data.error);
            }
            _this.loadingSign = false;
        });
    };
    SignatureBookComponent.prototype.unsignFile = function (attachment) {
        var _this = this;
        var collId;
        var resId;
        if (attachment.res_id == 0) {
            resId = attachment.res_id_version;
            collId = "res_version_attachments";
        }
        else if (attachment.res_id_version == 0) {
            resId = attachment.res_id;
            collId = "res_attachments";
        }
        this.http.put(this.coreUrl + 'rest/' + collId + '/' + resId + '/unsign', {}, {})
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (data.status == "OK") {
                _this.rightViewerLink = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=" + _this.resId + "&id=" + resId;
                _this.signatureBook.attachments[_this.rightSelectedThumbnail].viewerLink = _this.rightViewerLink;
                _this.signatureBook.attachments[_this.rightSelectedThumbnail].status = 'A_TRA';
                _this.signatureBook.attachments[_this.rightSelectedThumbnail].idToDl = resId;
                _this.signatureBook.resList[_this.signatureBook.resListIndex].allSigned = false;
            }
            else {
                alert(data.error);
            }
        });
    };
    SignatureBookComponent.prototype.backToBasket = function () {
        location.hash = "";
        location.reload();
    };
    SignatureBookComponent.prototype.backToDetails = function () {
        location.hash = "";
        location.search = "?page=details&dir=indexing_searching&id=" + this.resId;
    };
    SignatureBookComponent.prototype.changeLocation = function (resId, origin) {
        var _this = this;
        this.http.get(this.coreUrl + 'rest/res/' + resId + '/lock')
            .map(function (res) { return res.json(); })
            .subscribe(function (data) {
            if (!data.lock) {
                var path = "/" + _this.basketId + "/signatureBook/" + resId;
                _this.router.navigate([path]);
            }
            else {
                if (origin == "view") {
                    alert("Courrier verouillé par " + data.lockBy);
                }
                else if (origin == "action") {
                    _this.backToBasket();
                }
            }
        });
    };
    SignatureBookComponent.prototype.validForm = function () {
        if ($j("#signatureBookActions option:selected")[0].value != "") {
            unlockDocument(this.resId);
            valid_action_form('empty', 'index.php?display=true&page=manage_action&module=core', this.signatureBook.currentAction.id, this.resId, 'res_letterbox', 'null', 'letterbox_coll', 'page', false, [$j("#signatureBookActions option:selected")[0].value]);
        }
        else {
            alert("Aucune action choisie");
        }
    };
    SignatureBookComponent = __decorate([
        core_1.Component({
            templateUrl: 'js/angular/app/Views/signatureBook.html',
        }), 
        __metadata('design:paramtypes', [http_1.Http, router_1.ActivatedRoute, router_1.Router, core_1.NgZone])
    ], SignatureBookComponent);
    return SignatureBookComponent;
}());
exports.SignatureBookComponent = SignatureBookComponent;
