app.controller('ListController', ['$scope', 'Report', function($scope, Report){
	$scope.loading = true;
	Report.get('reports').then(function(response){
		if(response.status){
			$scope.reports = response.reports;
			$scope.loading = false;
		}
	}, function(err){
		console.log(err);
	});
}]);
