var mainApp = angular.module("AppModule", ["ngRoute"]);

mainApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
  $routeProvider
    .when("/baskets/:basketId/:resId", {
      templateUrl : "../../modules/visa/Views/signatureBook.html",
      controller  : "visaCtrl"
    });

  $locationProvider.hashPrefix('');
}]);

mainApp.filter('datetimeFormat', function($filter)
{
  return function(input)
  {
    if(input == null) {
      return "";
    }

    return $filter('date')(new Date(input), 'dd/MM/yyyy HH:mm');
  };
});
