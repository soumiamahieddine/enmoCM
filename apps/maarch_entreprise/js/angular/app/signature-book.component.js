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
var Rx_1 = require('rxjs/Rx');
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
    function SignatureBookComponent(http, route, router) {
        this.http = http;
        this.route = route;
        this.router = router;
        this.signatureBook = {
            currentAction: {},
            consigne: "",
            documents: [],
            attachments: []
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
        this.showAttachmentEditionPanel = false;
    }
    SignatureBookComponent.prototype.ngOnInit = function () {
        var _this = this;
        this.prepareSignatureBook();
        this.route.params.subscribe(function (params) {
            _this.resId = +params['resId'];
            _this.basketId = params['basketId'];
            lockDocument(_this.resId);
            Rx_1.Observable.interval(50000).subscribe(function () { return lockDocument(_this.resId); });
            _this.http.get('index.php?display=true&page=initializeJsGlobalConfig').map(function (res) { return res.json(); })
                .subscribe(function (data) {
                _this.coreUrl = data.coreurl;
                _this.http.get(_this.coreUrl + 'rest/' + _this.basketId + '/signatureBook/' + _this.resId)
                    .map(function (res) { return res.json(); })
                    .subscribe(function (data) {
                    _this.signatureBook = data;
                    if (_this.signatureBook.documents[0]) {
                        _this.leftViewerLink = _this.signatureBook.documents[0].viewerLink;
                    }
                    if (_this.signatureBook.attachments[0]) {
                        _this.rightViewerLink = _this.signatureBook.attachments[0].viewerLink;
                    }
                    _this.headerTab = 1;
                    _this.leftSelectedThumbnail = 0;
                    _this.rightSelectedThumbnail = 0;
                    _this.showLeftPanel = true;
                    _this.showResLeftPanel = true;
                    _this.showTopLeftPanel = false;
                    _this.showTopRightPanel = false;
                    _this.showAttachmentEditionPanel = false;
                });
            });
        });
    };
    SignatureBookComponent.prototype.prepareSignatureBook = function () {
        $j('#inner_content').remove();
        $j('#header').remove();
        $j('#viewBasketsTitle').remove();
        $j('#homePageWelcomeTitle').remove();
        $j('#footer').remove();
        $j('#container').width("98%");
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
        }
        else if (panel == "RESLEFT") {
            this.showResLeftPanel = !this.showResLeftPanel;
        }
    };
    SignatureBookComponent.prototype.prepareSignFile = function (attachment) {
        if (attachment.res_id == 0) {
            this.signatureBookSignFile(attachment.res_id_version, 1);
        }
        else if (attachment.res_id_version == 0) {
            this.signatureBookSignFile(attachment.res_id, 0);
        }
    };
    SignatureBookComponent.prototype.signatureBookSignFile = function (resId, type) {
        var _this = this;
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
            }
            else {
                alert(data.error);
            }
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
    SignatureBookComponent.prototype.changeLocation = function (resId) {
        var path = "/" + this.basketId + "/signatureBook/" + resId;
        this.router.navigate([path]);
    };
    SignatureBookComponent.prototype.validForm = function () {
        if ($j("#signatureBookActions option:selected")[0].value != "") {
            unlockDocument(this.resId);
            valid_action_form('empty', 'index.php?display=true&page=manage_action&module=core', this.signatureBook.currentAction.id, this.resId, 'res_letterbox', 'null', 'letterbox_coll', 'page', false, [$j("#signatureBookActions option:selected")[0].value]);
        }
    };
    SignatureBookComponent = __decorate([
        core_1.Component({
            templateUrl: 'js/angular/app/Views/signatureBook.html',
        }), 
        __metadata('design:paramtypes', [http_1.Http, router_1.ActivatedRoute, router_1.Router])
    ], SignatureBookComponent);
    return SignatureBookComponent;
}());
exports.SignatureBookComponent = SignatureBookComponent;
