app.controller('ListController', ['$scope', 'Report', function($scope, Report){
	Report.get('reports').then(function(response){
		if(response.status){
			$scope.reports = response.reports;
			/*angular.forEach($scope.reports, function(value, key){
				value.thumbnail = value.images[0];
			});*/
		}
	});
}]);
