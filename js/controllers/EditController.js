app.controller('EditController', ['$scope', '$rootScope', '$location', '$routeParams', 'Report', function($scope, $rootScope, $location, $routeParams, Report){
	$scope.report = {title: '', content: '', images: [], user: $rootScope.user};
	Report.get('reports/' + $routeParams.id).then(function(response){
		if(response.status){
			$scope.report.title = response.reports.title;
			$scope.report.content = response.reports.content;
			$scope.report.images = response.reports.images;
		}
	});
	$scope.put = function(report){
		Report.put('reports/' + $routeParams.id, report).then(function(response){
			if(response.status){
				$location.path('/reports');
			}
		});
	}
	$scope.delete = function(){
		Report.delete('reports/' + $routeParams.id).then(function(response){
			if(response.status){
				$location.path('/reports');
			}
		});
	}
	$scope.removeImage = function(index){
		var image = $scope.report.images[index];
		var name = image.name;
		Report.delete('report/' + $routeParams.id + '/images/' + name).then(function(response){
			if(response.status){
				//$scope.$apply(function(){
					$scope.report.images.splice(index, 1);
				//});
			}
		});
	}
	$scope.$on('doneFileModel', function(event, data){
		var fd = new FormData();
		fd.append('image', data);
		fd.append('memo', '');
		fd.append('user', $rootScope.user.email);
		Report.post('report/images', fd, {transformRequest: null, headers: {'Content-Type': undefined}}).then(function(response){
			if(response.status){
				var reader = new FileReader();
				reader.onload = function(){
					$scope.$apply(function(){
						$scope.report.images.push({name: response.image.fullpath, memo: response.image.memo, src: reader.result});
					});
				}
			reader.readAsDataURL(data);
			}
		});
	});
}]);
