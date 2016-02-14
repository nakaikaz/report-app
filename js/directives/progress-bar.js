app.directive('progressBar', function(){
  return {
    restrict: 'E',
    scope: {
      // 親スコープ内の変数をカスタム・ディレクティブのローカルスコープ属性にバインド
      show: '='
    },
    link: function(scope, element, attrs){
      // カスタム・ディレクティブの子要素に対して、フェード方法を記述した属性の値をクラス値として付加
      angular.forEach(element.children(), function(elm){
        var e = angular.element(elm).addClass(attrs.how);
      });
    },
    templateUrl: 'js/directives/progress-bar.html'
  }
});
