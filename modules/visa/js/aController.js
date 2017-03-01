"use strict";

mainApp.controller("visaCtrl", ["$scope", "$http", "$routeParams", "$interval", "NgTableParams", "$location",
  function($scope, $http, $routeParams, $interval, NgTableParams, $location) {

    var vm = this;

    function prepareSignatureBook() {
      if (typeof globalConfig.coreurl == "undefined") {
        InitializeJsGlobalConfig();
      }

      $j('#inner_content').remove();
      $j('#header').remove();
      $j('#viewBasketsTitle').remove();
      $j('#container').width("98%");
    }

    function getDatas(basketId, resId) {
      prepareSignatureBook();

      $http({
        method : 'GET',
        url    : globalConfig.coreurl + 'rest/' + basketId + '/signatureBook/' + resId,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).then(function successCallback(response) {

        $scope.signatureBook = response.data;
        $scope.signatureBook.rightSelectedThumbnail = 0;
        $scope.signatureBook.leftSelectedThumbnail = 0;
        if ($scope.signatureBook.attachments[0]) {
          $scope.signatureBook.rightViewerLink = $scope.signatureBook.attachments[0].viewerLink;
        }
        if ($scope.signatureBook.documents[0]) {
          $scope.signatureBook.leftViewerLink = $scope.signatureBook.documents[0].viewerLink;
        }
        $scope.signatureBook.headerTab = 1;
        $scope.signatureBook.showTopRightPanel = false;
        $scope.signatureBook.showTopLeftPanel = false;
        $scope.signatureBook.showAttachmentEditionPanel = false;

        $scope.historyTable = new NgTableParams({
            page: 1,
            count: 20,
            sorting: {
                  event_date: 'desc'
              }
          }, {
            total: $scope.signatureBook.histories.length,
            dataset: $scope.signatureBook.histories
          });

      }, function errorCallback(error) {
      });
    }

    $scope.changeSignatureBookLeftContent = function(id) {
      $scope.signatureBook.headerTab = id;
    };

    $scope.changeRightViewer = function(index) {
      if (index < 0) {
        $scope.signatureBook.showAttachmentEditionPanel = true;
      } else {
        $scope.signatureBook.rightViewerLink = $scope.signatureBook.attachments[index].viewerLink;
        $scope.signatureBook.showAttachmentEditionPanel = false;
      }
      $scope.signatureBook.rightSelectedThumbnail = index;
    };

    $scope.changeLeftViewer = function(index) {
      $scope.signatureBook.leftViewerLink = $scope.signatureBook.documents[index].viewerLink;
      $scope.signatureBook.leftSelectedThumbnail = index;
    };

    $scope.displayTopPanel = function(panel) {
      if (panel == "RIGHT") {
        $scope.signatureBook.showTopRightPanel = !$scope.signatureBook.showTopRightPanel;
        $scope.signatureBook.showTopRightPanel == true ? $j(".pjDetails").css("height", "100px") : $j(".pjDetails").css("height", "30px");
      } else if (panel == "LEFT") {
        $scope.signatureBook.showTopLeftPanel = !$scope.signatureBook.showTopLeftPanel;
        $scope.signatureBook.showTopLeftPanel == true ? $j(".pjDoc").css("height", "100px") : $j(".pjDoc").css("height", "30px");
        $scope.signatureBook.showTopLeftPanel == true ? $j("#leftPanelShowDocumentIframe").css("height", "80%") : $j("#leftPanelShowDocumentIframe").css("height", "90%");
      }
    };

    vm.prepareSignFile = function(attachment) {
      if (attachment.res_id == 0) {
        signatureBookSignFile(attachment.res_id_version, 1);
      } else if (attachment.res_id_version == 0) {
        signatureBookSignFile(attachment.res_id, 0);
      }
    };

    function signatureBookSignFile(resId, type) {
      var path = '';

      if (type == 0) {
        path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&id=' + resId;
      } else if (type == 1) {
        path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isVersion&id=' + resId;
      } else if (type == 2) {
        path = 'index.php?display=true&module=visa&page=sign_file&collId=letterbox_coll&isOutgoing&id=' + resId;
      }

      $http({
        method : 'GET',
        url    : path,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).then(function successCallback(response) {

        if (response.data.status == 0) {
          $scope.signatureBook.rightViewerLink = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=" + vm.resId + "&id=" + response.data.new_id;
          $scope.signatureBook.attachments[$scope.signatureBook.rightSelectedThumbnail].viewerLink = $scope.signatureBook.rightViewerLink;
          $scope.signatureBook.attachments[$scope.signatureBook.rightSelectedThumbnail].status = 'SIGN';
        } else {
          alert(response.data.error);
        }

      }, function errorCallback(error) {
      });

    }

    vm.unsignFile = function(attachment) {
      var collId;
      var resId;
      if (attachment.res_id == 0) {
        resId = attachment.res_id_version;
        collId = "res_version_attachments";
      } else if (attachment.res_id_version == 0) {
        resId = attachment.res_id;
        collId = "res_attachments";
      }

      $http({
        method : 'PUT',
        url    : globalConfig.coreurl + 'rest/' + collId + '/' + resId + '/unsign',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).then(function successCallback(response) {

        if (response.data.status == "OK") {
          $scope.signatureBook.rightViewerLink = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=" + vm.resId + "&id=" + resId;
          $scope.signatureBook.attachments[$scope.signatureBook.rightSelectedThumbnail].viewerLink = $scope.signatureBook.rightViewerLink;
          $scope.signatureBook.attachments[$scope.signatureBook.rightSelectedThumbnail].status = 'A_TRA';
        } else {
          alert(response.data.error);
        }

      }, function errorCallback(error) {
      });

    };

    $scope.backToBasket = function() {
      location.hash = "";
      location.reload();
    };

    $scope.changeLocation = function(resId) {
      $location.path(vm.basketId + "/signatureBook/" + resId);
    };

    $scope.validForm = function() {
      if ($j("#signatureBookActions option:selected")[0].value != "") {
        //$interval.cancel(intervalPromise);
        unlockDocument($routeParams.resId);

        valid_action_form(
          'empty',
          'http://127.0.0.1/maarch_trunk_git/apps/maarch_entreprise/index.php?display=true&page=manage_action&module=core',
          $scope.signatureBook.currentAction,
          $routeParams.resId,
          'res_letterbox',
          'null',
          'letterbox_coll',
          'page',
          false,
          [$j("#signatureBookActions option:selected")[0].value]
        );
      }
    };


    //Initialize View
    vm.basketId = $routeParams.basketId;
    vm.resId = $routeParams.resId;

    getDatas($routeParams.basketId, $routeParams.resId);

    lockDocument($routeParams.resId);
    $interval(function () {
      lockDocument($routeParams.resId);
    }, 50000);

}]);
