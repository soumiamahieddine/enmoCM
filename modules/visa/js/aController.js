"use strict";

mainApp.controller("visaCtrl", ["$scope", "$http", "$routeParams", "$interval", "NgTableParams", function($scope, $http, $routeParams, $interval, NgTableParams) {

  //var vm = this;

  function getDatas(res_id) {

    $j('#inner_content').remove();
    $j('#header').remove();
    $j('#viewBasketsTitle').remove();
    $http({
      method : 'GET',
      url    : globalConfig.coreurl + 'rest/signatureBook/' + res_id,
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
      console.log(error);
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

  $scope.backToBasket = function() {
    location.hash = "";
    location.reload();
  };

  $scope.validForm = function() {
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
  };



  //Initialize View
  getDatas($routeParams.resId);

  lockDocument($routeParams.resId);
  $interval(function () {
    lockDocument($routeParams.resId);
  }, 50000);

}]);