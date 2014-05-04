'use strict';

angular.module('somirApp', [
        'ngCookies',
        'ngResource',
        'ngSanitize',
        'ui.bootstrap',
        'ngRoute'
    ])
    .config(function ($routeProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'views/main.html',
                controller: 'MainCtrl'
            })
            .when('/about', {
                templateUrl: 'views/about.html',
                controller: 'AboutCtrl'
            })
            .when('/contacts', {
                templateUrl: 'views/contacts.html',
                controller: 'AboutCtrl'
            })
            .otherwise({
                redirectTo: '/'
            });
    });


// Register a response interceptor for AJAX errors.
angular.module('somirApp').config(function ($httpProvider) {
    $httpProvider.responseInterceptors.push('errorHttpInterceptor');
});

// Create the service that will response to AJAX errors.
angular.module('somirApp').factory('errorHttpInterceptor', function ($rootScope, $q, $location) {
    return function (promise) {
        return promise.then(function (response) {
            return response;
        }, function (response) {
            // Handle redirect to login.
            if (response.status === 401) {
                if ($location.path() !== '/login') {
                    $location.path('/login').search({
                        returnUrl: $location.path()
                    });
                }
            } else {
                $rootScope.lastError = {
                    status: response.status,
                    data: response.data
                };

                $location.path('/error');
            }

            return $q.reject(response);
        });
    };
});
