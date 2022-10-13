/*
 ** This file is part of OpenDataClient.
 **
 ** OpenDataClient is free software: you can redistribute it and/or modify
 ** it under the terms of the GNU General Public License as published by
 ** the Free Software Foundation, either version 3 of the License, or
 ** (at your option) any later version.
 **
 ** OpenDataClient is distributed in the hope that it will be useful,
 ** but WITHOUT ANY WARRANTY; without even the implied warranty of
 ** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 ** GNU General Public License for more details.
 **
 ** You should have received a copy of the GNU General Public License
 ** along with OpenDataClient. If not, see <http://www.gnu.org/licenses/>.
 **
 ** Copyright (C) 2016 OpenDataClient - Donato Pirozzi (donatopirozzi@gmail.com)
 ** Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 ** License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 **/

/*if (typeof define !== 'function') {
    if (typeof require !== 'undefined')
        var define = require('amdefine')(module);
    else
        var define = function (fnc) { return fnc; };
}

define(function(require, exports, module) {
    if (typeof module !== 'undefined')
        module.exports = CKANClient;
    else
        return CKANClient;
});

//if (typeof define !== 'function') { var define = require('amdefine')(module); }
//debugger;*/

function CKANClient(platformUrl, token) {
    this.baseurl = platformUrl;
    this.baseApiUrl = platformUrl + "/api/3/action/";
    this.authToken = token;
}//EndFunction.

CKANClient.prototype = (function() {

    var _processListOfDatasets = function(jsonResponse, userCallback) {
        var datasets = [];

        var jsonResults = jsonResponse.result.results;

        for (var i=0; i<jsonResults.length; i++) {
            var jsonResult = jsonResults[i];
            var jsonResources = jsonResult.resources;

            //The dataset to retrieve to the calling function.
            var rtnDataset = {
                title: jsonResult.title,
                licenseId: jsonResult.license_id,
                licenseName: jsonResult.license_title,
                resources: []
            };

            //TODO: Check here whether the dataset is private and active.
            //console.log("state " + jsonResult.state);
            //console.log("private " + jsonResult.private);

            for (var j=0; j<jsonResources.length; j++) {
                var jsonResource = jsonResources[j];

                //console.log("state " + jsonResource.state);

                var parsedUrl = URLUtils.ParseString(jsonResource.url);
                var pageUrl = parsedUrl.host + "/dataset/" + jsonResult.name + "/resource/" + jsonResource.id;

                var rtnResource = {
                    id: jsonResource.id,
                    name: jsonResource.name,
                    format: jsonResource.format,
                    url: jsonResource.url,
                    pageUrl: pageUrl
                };
                rtnDataset.resources.push(rtnResource)
            }//EndForJ.

            datasets.push(rtnDataset);
        }//EndForI.

        userCallback(datasets);
    };

    var _performHttpRequest = function (targetUrl, callback) {
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function (_xmlHttpRequest) {
            const _responseText = _xmlHttpRequest.currentTarget.responseText;
            if (this.status >= 200 && this.status < 300)
                callback( _responseText, null );
            else
                callback( _responseText, { status: xhttp.status, statusText: xhttp.statusText } );
        };
        xhttp.onerror = function (err) {
            callback( null, { status: xhttp.status, statusText: xhttp.statusText } );
        };
        xhttp.open("GET", targetUrl, true);
        xhttp.send(null);
    };

    //Public object content.
    return {
        constructor: CKANClient,

        /**
         * It support the version 3 of the CKAN API.
         * @param baseUrl
         * @param userCallback
         */
        listDatasets: function (baseUrl, userCallback, options) {
            if (typeof options === 'undefined')
                options = { jsonp: false };

            var apiListDataset = baseUrl + "/api/3/action/package_search" + "?rows=100000";

            if (options.jsonp) {
                URLUtils.JSONP(apiListDataset, function (jsonResponse) {
                    _processListOfDatasets(jsonResponse, userCallback);
                });
            } else
                //Make http request.
                URLUtils.HTTPGetAsync(apiListDataset, function(responseText) {
                    var jsonResponse = JSON.parse(responseText);
                    _processListOfDatasets(jsonResponse, userCallback);
                }, function () {
                    //Error, it try to use JSONP, otherwise retrives the error.
                    console.log("CKANClient API: failed to load " + baseUrl + ", trying to use JSONP.");
                    URLUtils.JSONP(apiListDataset, function (jsonResponse) {
                        _processListOfDatasets(jsonResponse, userCallback);
                    });
                });
        },//EndFunction.

        showPackage: function(package_id, callback) {
            var targetUrl = this.baseApiUrl + "package_show";

            const key = package_id.toLowerCase();
            var data = {
                id: key
            }

            const xhttp = new XMLHttpRequest();
            xhttp.onload = function (_xmlHttpRequest) {
                const _responseText = _xmlHttpRequest.currentTarget.responseText;
                if (this.status >= 200 && this.status < 300)
                    callback( _responseText, null );
                else
                    callback( _responseText, { status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.onerror = function (err) {
                callback( null, { status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.open("POST", targetUrl, true);
            xhttp.setRequestHeader('X-CKAN-API-Key', this.authToken);//Authentication.
            // xhttp.setRequestHeader('Content-Type', 'application/json'); //aaaa
            xhttp.send(JSON.stringify(data));
        },//EndFunction.

        updatePackage: function (package_id, data, callback) {
            const $self = this;
            this.showPackage(package_id, function (response) {
                var _jsonResponse = JSON.parse(response);
                if (_jsonResponse.success == false) {
                    const errorObj = { success: false, statusText: "Nothing to update", responseText: response };
                    callback (errorObj);
                    return;
                }

                var targetUrl = $self.baseApiUrl + "package_update";
                var _ckanPackageData = _jsonResponse.result;

                _ckanPackageData.title = data.title;
                _ckanPackageData.notes = data.notes;
                _ckanPackageData.description = data.description;
                _ckanPackageData.author = data.author;
                _ckanPackageData.author_email = data.author_email;
                _ckanPackageData.maintainer = data.maintainer;
                _ckanPackageData.maintainer_email = data.maintainer_email;
                _ckanPackageData.version = data.version;
                _ckanPackageData.language = data.language;
                _ckanPackageData.url = data.url;
                _ckanPackageData.license_id = data.license_id;

                var _sCkanPackageData = JSON.stringify(_ckanPackageData);
                $self.makeHTTPRequest(targetUrl, _sCkanPackageData, callback);
            });
        },//EndFunction.

        updateResource: function (resource_id, datafile, metadata, callback) {
            const targetUrl = this.baseApiUrl + "resource_update";
            if (typeof metadata.format === "string")
                metadata.format = metadata.format.toUpperCase();

            var formData = new FormData();
            formData.append('id', resource_id);
            formData.append('package_id', metadata.package_id.toLowerCase());
            formData.append('url', metadata.url);
            formData.append('format', metadata.format);
            formData.append('name', metadata.name);
            formData.append('description', metadata.description);
            formData.append('upload', datafile);

            this.makeHTTPRequest(targetUrl, formData, callback, { ContentType: null });
        },//EndFunction.

        makeHTTPRequest: function(targetUrl, data, callback, options) {
            if (data.hasOwnProperty("name"))
                data.name =  data.name.replace(/ /g, '_').toLocaleLowerCase();

            const xhttp = new XMLHttpRequest();
            xhttp.onload = function (_xmlHttpRequest) {
                const _responseText = _xmlHttpRequest.currentTarget.responseText;
                if (this.status >= 200 && this.status < 300)
                    callback({ success: true, responseText: _responseText });
                else
                    callback({ success: false, responseText: _responseText, status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.onerror = function (err) {
                callback( { success: false, status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.open("POST", targetUrl, true);
            xhttp.setRequestHeader('X-CKAN-API-Key', this.authToken);//Authentication.
            if (typeof options === 'undefined') {//Default.
                xhttp.setRequestHeader('Content-Type', 'application/json');//Authentication.
            } else {
                if (options.hasOwnProperty("ContentType") && options.ContentType != null)
                    xhttp.setRequestHeader('Content-Type', options.ContentType);
            }
            xhttp.send(data);
        },//EndFunction.

        createPackage: function(data, callback) {
            var targetUrl = this.baseApiUrl + "package_create";

            data.name =  data.name.replace(/ /g, '_').toLocaleLowerCase();

            const xhttp = new XMLHttpRequest();
            xhttp.onload = function (_xmlHttpRequest) {
                const _responseText = _xmlHttpRequest.currentTarget.responseText;
                if (this.status >= 200 && this.status < 300)
                     callback( _responseText, null );
                else
                    callback( _responseText, { status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.onerror = function (err) {
                callback( null, { status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.open("POST", targetUrl, true);
            xhttp.setRequestHeader('X-CKAN-API-Key', this.authToken);//Authentication.
            xhttp.setRequestHeader('Content-Type', 'application/json');//Authentication.
            xhttp.send(JSON.stringify(data));
        },//EndFunction.

        createResource: function (package_id, datafile, metadata, callback) {
            const targetUrl = this.baseApiUrl + "resource_create";
            if (typeof metadata.format === "string")
                metadata.format = metadata.format.toUpperCase();

            var _url = 'http://www.fittizio_' + Math.floor((Math.random()*10000)) + '.net'
            var formData = new FormData();
            formData.append('package_id', package_id);
            formData.append('url', _url);
            formData.append('format', metadata.format);
            formData.append('name', metadata.name);
            formData.append('description', metadata.description);
            
            formData.append('upload', datafile);

            const xhttp = new XMLHttpRequest();
            xhttp.onload = function (_xmlHttpRequest) {
                const _responseText = _xmlHttpRequest.currentTarget.responseText;
                if (this.status >= 200 && this.status < 300)
                    callback( _responseText, null );
                else
                    callback( _responseText, { status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.onerror = function (err) {
                callback( null, { status: xhttp.status, statusText: xhttp.statusText } );
            };//EndCallback.
            xhttp.open("POST", targetUrl, true);//true for async.
            xhttp.setRequestHeader('X-CKAN-API-Key', this.authToken);//Authentication.
            xhttp.send(formData);
        },//EndFunction.

        createResourceCSV: function (package_id, datafile, metadata, callback) {
            const targetUrl = this.baseApiUrl + "resource_create";

            const filename = metadata.name;

            var _url = 'http://www.fittizio_' + Math.floor((Math.random()*10000)) + '.net'
            var formData = new FormData();
            formData.append('package_id', package_id);
            //formData.append('id', resourceid);
            formData.append('url', _url);
            formData.append('format', 'CSV');
            //formData.append('Format', 'CSV');
            //formData.append('mimetype', mimetype);
            formData.append('name', metadata.title);
            
            //formData.append('state', 'active');
            //formData.append('hash', hash);
            formData.append('description', metadata.description);
            formData.append('upload', datafile);
            
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function (_xmlHttpRequest) {
                const _responseText = _xmlHttpRequest.currentTarget.responseText;
                if (this.status >= 200 && this.status < 300)
                    callback( _responseText, null );
                else
                    callback( _responseText, { status: xhttp.status, statusText: xhttp.statusText } );
            };
            xhttp.onerror = function (err) {
                callback( null, { status: xhttp.status, statusText: xhttp.statusText } );
            };//EndCallback.
            xhttp.open("POST", targetUrl, true);//true for async.
            xhttp.setRequestHeader('X-CKAN-API-Key', this.authToken);//Authentication.
            xhttp.send(formData);
        },//EndFunction.

        createResourceCSVFromURL: function (package_id, url, callback) {
            _performHttpRequest(url, function (_csvResponse, _csvError) {
                debugger;
            });
        }//EndFunction.

    };
})();
