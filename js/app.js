var app = angular.module('reportApp', ['ui.bootstrap', 'ngRoute']);
app.config(['$routeProvider', function($routeProvider){
	$routeProvider
	.when('/', {
		controller: 'HomeController',
		templateUrl: 'views/home.html'
	})
	.when('/signup', {
		title: 'サインアップ',
		controller: 'AppController',
		templateUrl: 'views/signup.html'
	})
	.when('/login', {
		title: 'ログイン',
		controller: 'AppController',
		templateUrl: 'views/login.html'
	})
	.when('/reports', {
		title: 'レポート一覧',
		controller: 'ListController',
		templateUrl: 'views/list.html'
	})
	.when('/report/add', {
		title: 'レポートの追加',
		controller: 'AddController',
		templateUrl: 'views/add.html'
	})
	.when('/report/edit/:id', {
		title: 'レポートの編集',
		controller: 'EditController',
		templateUrl: 'views/edit.html'
	})
	.otherwise({
		//redirectTo: '/'
		template: "this route isn't set!"
	});
}]);

app.constant('API', {
	baseUrl: 'api/'
});
app.constant('APP', {
	title: 'レポート'
});

app.run(['$rootScope', '$location', 'APP', 'Auth', function($rootScope, $location, APP, Auth){
	$rootScope.$on('$routeChangeSuccess', function(event, next, previous){
		document.title = next.title + APP.title;
	});
	$rootScope.$on('$routeChangeStart', function(event, next, current){
		$rootScope.authenticated = false;
		$rootScope.user = {};
		Auth.get('session').then(function(response){
			if(response.id){
				$rootScope.authenticated = true;
				$rootScope.user.id = response.id;
				$rootScope.user.name = response.name;
				$rootScope.user.email = response.email;
			}else{
				if(typeof next === 'undefined'){
					$location.path('/login');
				}else if(next.$$route){
					var nextUrl = next.$$route.originalPath;
					if(nextUrl != '/signup' && nextUrl !='/login'){
						$location.path('/login');
					}
				}
			}
		});
	});
}]);
