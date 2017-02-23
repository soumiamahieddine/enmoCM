mainApp.controller("visaCtrl", ["$scope", "$http", "$routeParams", "$interval", function($scope, $http, $routeParams, $interval) {

  $scope.getDatas = function(res_id) {

    $j('#inner_content').remove();
    $http({
      method : 'GET',
      url    : globalConfig.coreurl + 'rest/signatureBook/' + res_id,
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then(function successCallback(response) {

      $scope.signatureBook = response.data;
      $scope.signatureBook.rightSelectedThumbnail = 0;
      $scope.signatureBook.rightViewerLink = $scope.signatureBook.attachments[0].viewerLink;
      $scope.signatureBook.leftSelectedThumbnail = 0;
      $scope.signatureBook.leftViewerLink = $scope.signatureBook.documents[0].viewerLink;
      $scope.signatureBook.headerTab = 1;

    }, function errorCallback(error) {
      console.log(error);
    });
  };

  $scope.changeSignatureBookLeftContent = function(id) {
    $scope.signatureBook.headerTab = id;
  };

  $scope.changeRightViewer = function(index) {
    $scope.signatureBook.rightViewerLink = $scope.signatureBook.attachments[index].viewerLink;
    $scope.signatureBook.rightSelectedThumbnail = index;
  };

  $scope.changeLeftViewer = function(index) {
    $scope.signatureBook.leftViewerLink = $scope.signatureBook.documents[index].viewerLink;
    $scope.signatureBook.leftSelectedThumbnail = index;
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
  $scope.getDatas($routeParams.resId);

  lockDocument($routeParams.resId);
  $interval(function () {
    lockDocument($routeParams.resId);
  }, 50000);

}]);