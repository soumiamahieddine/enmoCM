var mainApp = angular.module("AppModule", ["ngRoute"]);

mainApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
  $routeProvider
    .when("/baskets/:basketId/:resId", {
      templateUrl : "../../modules/visa/Views/signatureBook.html",
      controller  : "visaCtrl"
    });

  $locationProvider.hashPrefix('');
}]);
