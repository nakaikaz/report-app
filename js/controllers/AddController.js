app.controller('AddController', ['$scope', '$rootScope', '$location', 'Report', function($scope, $rootScope, $location, Report){
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
						$scope.report.images.push({name: response.image.name, memo: response.image.memo, src: reader.result});
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
	$scope.removeImage = function(index){
		var image = $scope.report.images[index];
		var name = image.name;
		Report.delete('report/image/' + image.name).then(function(response){
			if(response.status){
				$scope.report.images.splice(index, 1);
			}
		}, function(err){
			console.log(err);
		});
	}
	$scope.add = function(){
		$scope.$emit('loading', true);
		Report.post('report', $scope.report).then(function(response){
			if(response.status){
				$location.path('/reports');
			}
			$scope.$emit('loading', false);
		}, function(err){
			console.log(err);
		});
	};

}]);
