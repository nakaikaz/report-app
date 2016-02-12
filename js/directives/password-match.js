app.directive('passwordMatch', function(){
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function(scope, element, attrs, control){
			scope.$watch(function(){
				var e1 = scope.$eval(attrs.ngModel);
				var e2 = scope.$eval(attrs.passwordMatch);
				return e1 == e2;
			}, function(n){
				control.$setValidity('passwordNoMatch', n);
			});
		}
	}
});
