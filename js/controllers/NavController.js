app.controller('NavController', ['$scope', '$http', function($scope, $http){
	var user = $scope.$parent.user;
	$scope.logout = function(){
		$http.post('http://api.local/logout', user)
		.success(function(data, status, header, config){
			consoloe.log(data);
		})
		.error(function(data, status, header, config){
			console.log(data);
		});
	}
}]);
