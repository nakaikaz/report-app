app.directive('fileModel', [function(){
	return {
		restrict: 'A',
		link: function(scope, element, attrs){
			element.bind('change', function(){
				scope.$apply(function(){
					scope.$broadcast('doneFileModel', element[0].files[0]);
				});
			});
		}
	};
}]);

