require.config({
  baseUrl: '/es_client/vendor',
  waitSeconds: 600,
  paths: {
    es_client: '..',
    test: '../test/unit'
  },
  shim:{
    'underscore':{
      exports: '_'
    },
    'backbone':{
      deps: ['underscore','jquery'],
      exports: 'Backbone'
    },
    'socket.io-client':{
      exports: 'io'
    },
    'sinon':{
      exports: 'sinon'
    },
    'validator':{
      exports: 'sanitize'
    },
    'es_expression':{
      exports: 'es_expression'
    },
    'sockjs-client':{
      exports: 'SockJS'
    },
    'xregexp':{
      exports: 'XRegExp'
    },
    'lookbehind':{
      exports: 'lookbehind'
    },
    'i18next':{
      exports: 'i18n'
    }/*,
    'ol':{
      exports: 'ol'
    },
    'ol3-geocoder':{
      deps: ['ol'],
      exports: 'Geocoder'
    }*/
  }
});
