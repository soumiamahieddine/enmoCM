mainApp.controller("basketCtrl", ["$scope", "$http", "$compile", function($scope, $http, $compile) {

  $scope.gto = 'jaki';


  $scope.getView = function(res_id, service, module) {

    $http({
      method : 'POST',
      url    : globalConfig.coreurl + 'rest.php?module=' + module + '&service=' + service + '&method=getViewDatas',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      data   : $j.param({
        resId : res_id
      })
    }).then(function successCallback(response) {
      var elem = angular.element(response.data.result.view);

      $j('#divList').html(elem);
      $compile(elem)($scope);

    }, function errorCallback(response) {
      console.log(response);
    });
  };



}]);