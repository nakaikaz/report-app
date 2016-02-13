app.directive('progressBar', function(){
  return {
    restrict: 'E',
    scope: {
      show: '='
    },
    templateUrl: 'js/directives/progress-bar.html'
  }
});
