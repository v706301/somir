'use strict';

angular.module('somirApp')
    .directive('menuGenerator', function () {
        return {
            templateUrl: './views/directives/menu.html',
            restrict: 'EA',
            replace: true,
            scope: false,
            link: function postLink(scope, element, attrs) {
                //element.text('this is the MenuGenerator directive');
            }
        };
    });
