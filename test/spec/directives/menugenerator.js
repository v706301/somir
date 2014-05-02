'use strict';

describe('Directive: MenuGenerator', function () {

  // load the directive's module
  beforeEach(module('somirApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<-menu-generator></-menu-generator>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the MenuGenerator directive');
  }));
});
