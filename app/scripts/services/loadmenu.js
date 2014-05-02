'use strict';

angular.module('somirApp')
    .service('Loadmenu', function Loadmenu($http, $routeParams, $log) {
        var url = '/handlers/menu.php';

        this.get = function () {
            return $http.get(url);
        };
    });
