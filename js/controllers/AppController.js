app.controller('AppController', ['$scope', '$rootScope', '$location', '$window', 'Auth', function($scope, $rootScope, $location, $window, Auth){
    $scope.loading = false;
    $scope.loginError = false;
    $scope.signupError = false;
    $scope.preSignupError = false;
    $scope.login = {};
    $scope.signup = {};
    $scope.presignup = {};
    $scope.donePreSignup = false;
    $scope.canSignup = false;
    $scope.token = '';
    var token = $location.search()['t'];
    if(token){
        Auth.get('signup?t=' + token ).then(function(response){
            if(response.status){
                $scope.canSignup = response.canSignup;
                $scope.token = response.token;
            }
        }, function(err){
            console.log(err);
        });
    }
    $scope.preSignUp = function(){
        $scope.loading = true;
        Auth.post('presignup', {user: $scope.presignup}).then(function(response){
            if(response.status){
                $scope.donePreSignup = true;
            }else{
                $scope.preSignupError = true;
            }
            $scope.loading = false;
        }, function(err){
            console.log(err);
        });
    };
    $scope.signUp = function(){
        if($scope.signupForm.$invalid){
            return;
        }
        Auth.post('signup', {user: $scope.signup, token: $scope.token}).then(function(response){
            if(response.status){
                $scope.token = '';
                $window.location.href = '/report-app/#/reports';
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
            $window.location.href = '/report-app/#/account/login';
        });
    };
    $scope.$on('loading', function(event, args){
        $scope.loading = args;
    });
}]);
