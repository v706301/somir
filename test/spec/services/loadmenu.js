'use strict';

describe('Service: Loadmenu', function () {

  // load the service's module
  beforeEach(module('somirApp'));

  // instantiate service
  var Loadmenu;
  beforeEach(inject(function (_Loadmenu_) {
    Loadmenu = _Loadmenu_;
  }));

  it('should do something', function () {
    expect(!!Loadmenu).toBe(true);
  });

});
