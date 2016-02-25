app.controller('AppController', ['$scope', '$rootScope', '$location', 'Auth', function($scope, $rootScope, $location, Auth){
    $scope.loading = false;
    $scope.loginError = false;
    $scope.signupError = false;
    $scope.preSignupError = false;
    $scope.login = {};
    $scope.signup = {};
    $scope.presignup = {};
    $scope.donePreSignup = false;
    $scope.preSignUp = function(){
        Auth.post('presignup', {user: $scope.presignup}).then(function(response){
            if(response.status){
                $scope.donePreSignup = true;
            }else{
                $scope.preSignupError = true;
            }
        }, function(err){
            console.log(err);
        });
    };
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
        }, function(err){
            console.log(err);
        });
    }
    $scope.logout = function(){
        Auth.get('logout').then(function(response){
            $location.path('login');
        });
    };
    $scope.$on('loading', function(event, args){
        $scope.loading = args;
    });
}]);
