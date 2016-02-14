app.controller('ListController', ['$scope', 'Report', function($scope, Report){
	//$scope.$parent.loading = true;
	$scope.$emit('loading', true);
	Report.get('reports').then(function(response){
		if(response.status){
			$scope.reports = response.reports;
			//$scope.$parent.loading = false;
			$scope.$emit('loading', false);
		}
	}, function(err){
		console.log(err);
	});
}]);
