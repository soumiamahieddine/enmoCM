mainApp.controller("visaCtrl", ["$scope", "$http", "$routeParams", function($scope, $http, $routeParams) {

  $scope.getDatas = function(res_id) {

    $j('#inner_content').remove();
    $http({
      method : 'POST',
      url    : globalConfig.coreurl + 'rest.php?module=visa&service=Visa_SignatureBook_Service&method=getViewDatas',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      data   : $j.param({
        resId : res_id
      })
    }).then(function successCallback(response) {
      $scope.signatureBook = response.data.result;

    }, function errorCallback(response) {
      console.log(response);
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

  $scope.getDatas($routeParams.resId);
}]);