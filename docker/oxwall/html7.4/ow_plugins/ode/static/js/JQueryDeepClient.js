/**
 * Created by Luigi Serra on 10/06/2015.
 */

var ComponentService =
{
    getComponent: function(params){

        var link;
        var component = params.component;
        var data = params.data;

        $.ajax({
            url : params.deep_url + params.component,
            dataType : 'json',
            complete : function (data) {

                try {
                    var resp = JSON.parse(data.responseText);
                    link = '<link rel="import" href="' + resp.bridge_link + resp.component_link + '">';
                    //Build jsonPath query string
                    var query = "";
                    for(var i=0;i < params.fields.length;i++){
                        var query_elements = params.fields[i].split(',');
                        query += "$";
                        for(var j=0; j < query_elements.length - 1;j++){
                            query += "['" + query_elements[j] + "']";
                        }
                        query += "[*]" + "['" + query_elements[query_elements.length - 1] + "']";
                        query += "###";
                    }

                    query = (query == "") ? "" : query.substring(0, this.query.length - 3);

                    //Build datalet injecting html code
                    var datalet_code = link + '<' + params.component;
                    var keys = Object.keys(params.params);
                    for(var i = 0; i < keys.length; i++){
                        datalet_code += ' ' + keys[i] + '="' + params.params[keys[i]] +'"';
                    }
                    datalet_code += ' query="' + query + '"></' + params.component + '>';

                    (params.placeHolder.constructor == HTMLElement) ? $(params.placeHolder).html(datalet_code) :/*Injection from Web Component*/
                        $("#" + params.placeHolder).html(datalet_code);/*Injection from a static web page*/

                } catch (e){
                    var resp = {
                        status: 'error',
                        data: 'Unknown error occurred: [' + request.response + ']'
                    };

                    console.log(resp);
                }
            }
        });

    }

};
