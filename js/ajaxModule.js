'use strict';

// A-> $http cette fonction est implémentée pour respecter le patron
// de conception (pattern) Adaptateur
export function $http(url){
 
    // Un exemple d'objet
    var core = {

        // La méthode qui effectue la requête AJAX
        ajax : function (method, url, args) {

            // On établit une promesse en retour
            var promise = new Promise( function (resolve, reject) {

                // On instancie un XMLHttpRequest
                var client = new XMLHttpRequest();
                var uri = '';

                if (args) {
                    //uri += '?';
                    var argcount = 0;
                    for (var key in args) {
                        if (args.hasOwnProperty(key)) {
                            if (argcount++) {
                                uri += '&';
                            }
                            uri += encodeURIComponent(key) + '=' + encodeURIComponent(args[key]);
                        }
                    }
                }
                if (method === 'GET')
                    client.open(method, url + '?' + uri);
                else
                    client.open(method, url);
                
                if (method === 'POST')
                    client.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                client.send(uri);

                client.onload = function () {
                    if (this.status >= 200 && this.status < 300) {
                        // On utilise la fonction "resolve" lorsque this.status vaut 2xx
                        resolve(this.response);
                    } else {
                        // On utilise la fonction "reject" lorsque this.status est différent de 2xx
                        reject(this.statusText);
                    }
                };

                client.onerror = function () {
                    reject(this.statusText);
                };
            });

            // Return the promise
            return promise;
        }
    };

    // Pattern adaptateur
    return {
        'get' : function(args) {
            return core.ajax('GET', url, args);
        },
        'post' : function(args) {
            return core.ajax('POST', url, args);
        },
        'put' : function(args) {
            return core.ajax('PUT', url, args);
        },
        'delete' : function(args) {
            return core.ajax('DELETE', url, args);
        }
    };
};