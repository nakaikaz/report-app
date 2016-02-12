app.controller('AppController', ['$scope', '$rootScope', '$location', 'Auth', function($scope, $rootScope, $location, Auth){
	$scope.loginError = false;
	$scope.signupError = false;
	$scope.login = {};
	$scope.signup = {};
	$scope.signUp = function(){
		if($scope.signupForm.$invalid){
			return;
		}
		Auth.post('signup', {user: $scope.signup}).then(function(response){
			if(response.status){
				$location.path('reports');
			}else{
				$scope.signupError = true;
			}
		}, function(err){
			console.log(err);
		});
	};
	$scope.doLogin = function(){
		Auth.post('login', {user: $scope.login}).then(function(response){
			if(response.status){
				$location.path('reports');
			}else{
				$scope.loginError = true;
			}
		});
	}
	$scope.logout = function(){
		Auth.get('logout').then(function(response){
			$location.path('login');
		});
	};
}]);
