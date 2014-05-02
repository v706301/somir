'use strict';

angular.module('somirApp')
  .controller('MenuCtrl', function ($scope, Loadmenu) {
        $scope.menu = null;

        function load(){
            Loadmenu.get().success(function(result){
                $scope.menu = result;
            });
        }

        load();
  });
