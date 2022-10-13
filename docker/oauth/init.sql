use oauth2;
insert into oauth_scopes (scope, is_default) values ('authenticate', 1);

-- insert into oauth_clients (client_id, client_secret, redirect_uri,
--             grant_types, scope, title, home_uri,
--             init_oauth_uri, auto_authorize) values ('spod-website', @client_secret, 'http://ns3070762.ip-37-59-57.eu/spodoauth2connect/oauth',
--         'authorization_code', 'authenticate', ' ', ' ',
--         'http://ns3070762.ip-37-59-57.eu/spodoauth2connect/begin', 1);



insert into oauth_clients (client_id, client_secret, redirect_uri,
            grant_types, scope, title, home_uri,
            init_oauth_uri, auto_authorize) values ('spod-website', @client_secret, 'http://localhost/spodoauth2connect/oauth',
        'authorization_code', 'authenticate', ' ', ' ',
        'http://localhost/spodoauth2connect/begin', 1);

