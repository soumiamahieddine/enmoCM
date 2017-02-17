var mainApp = angular.module("mainApp", ["ngRoute"]);

mainApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
  $routeProvider
    .when("/baskets/evis/:resId", {
      templateUrl : "../../modules/visa/Views/signatureBook.html",
      controller  : "visaCtrl"
    });

  $locationProvider.hashPrefix('');
}]);
