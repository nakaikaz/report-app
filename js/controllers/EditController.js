app.controller('EditController', ['$scope', '$rootScope', '$location', '$routeParams', 'Report', function($scope, $rootScope, $location, $routeParams, Report){
	$scope.report = {title: '', content: '', images: [], user: $rootScope.user};
	$scope.$on('doneFileModel', function(event, data){
		$scope.$emit('loading', true);
		var fd = new FormData();
		fd.append('image', data);
		fd.append('memo', '');
		fd.append('email', $rootScope.user.email);
		Report.post('report/images', fd, {transformRequest: null, headers: {'Content-Type': undefined}}).then(function(response){
			if(response.status){
				var reader = new FileReader();
				reader.onload = function(){
					$scope.$apply(function(){
						$scope.report.images.push({name: response.image.name, path: response.image.path, memo: response.image.memo, src: reader.result});
					});
				}
				reader.readAsDataURL(data);
			}else{
				console.log(response);
			}
			$scope.$emit('loading', false);
		}, function(err){
			console.log(err);
		});
	});
	Report.get('reports/' + $routeParams.id).then(function(response){
		$scope.$emit('loading', true);
		if(response.status){
			$scope.report.title = response.reports.title;
			$scope.report.content = response.reports.content;
			$scope.report.images = response.reports.images;
		}
		$scope.$emit('loading', false);
	});
	$scope.put = function(report){
		$scope.$emit('loading', true);
		Report.put('reports/' + $routeParams.id, report).then(function(response){
			if(response.status){
				$location.path('/reports');
			}
			$scope.$emit('loading', false);
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
}]);
