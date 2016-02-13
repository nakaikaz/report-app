app.directive('progressBar', function(){
  return {
    restrict: 'E',
    scope: {
      show: '='
    },
    link: function(scope, element, attrs){
      angular.forEach(element.children(), function(elm){
        var e = angular.element(elm).addClass(attrs.how);
      });
    },
    templateUrl: 'js/directives/progress-bar.html'
  }
});
