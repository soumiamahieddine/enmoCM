var mainApp = angular.module("AppModule", ["ngRoute", "ngTable"]);

mainApp.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
  $routeProvider
    .when("/signatureBook/:resId", {
      templateUrl   : "../../modules/visa/Views/signatureBook.html",
      controller    : "visaCtrl"
      //controllerAs  : "vm"
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
