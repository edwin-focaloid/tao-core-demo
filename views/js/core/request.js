/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

/**
 * Common HTTP request wrapper to get data from TAO.
 * This suppose the endpoint to match the following criteria :
 *   - Restful endpoint
 *   - contentType : application/json
 *   - the responseBody:
 *      { success : true, data : [the results]}
 *      { success : false, errorCode: 412, errorMsg : 'Something went wrong' }
 *   - 204 for empty content
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'context',
    'core/promise',
    'core/promiseQueue',
    'core/tokenHandler',
    'ui/feedback'
], function($, _, __, context, Promise, promiseQueue, tokenHandlerFactory, feedback) {
    'use strict';

    var tokenHandler = tokenHandlerFactory();

    var queue = promiseQueue();

    /**
     * Create a new error based on the given response
     * @param {Object} response - the server body response as plain object
     * @param {String} fallbackMessage - the error message in case the response isn't correct
     * @param {Number} httpCode - the response HTTP code
     * @returns {Error} the new error
     */
    var createError = function createError(response, fallbackMessage, httpCode) {
        var err;
        if(response && response.errorCode) {
            err = new Error(response.errorCode + ' : ' + (response.errorMsg || response.errorMessage || response.error));
        } else {
            err = new Error(fallbackMessage);
        }
        err.response = response;
        if (httpCode) {
            err.code = httpCode;
        }
        return err;
    };

    /**
     * Request content from a TAO endpoint
     * @param {Object} options
     * @param {String} options.url - the endpoint full url
     * @param {String} [options.method = 'GET'] - the HTTP method
     * @param {Object} [options.data] - additional parameters (if method is 'POST')
     * @param {Object} [options.headers] - the HTTP headers
     * @param {String} [options.contentType] - will usually be 'json'
     * @param {Boolean} [options.noToken = false] - if true, disables the token requirement
     * @param {Boolean} [options.background] - if true, the request should be done in the background, which in practice does not trigger the global handlers like ajaxStart or ajaxStop
     * @param {Boolean} [options.sequential] - if true, the request must join a queue to be run sequentially
     * @param {Number}  [options.timeout] - timeout in seconds for the AJAX request
     * @returns {Promise} resolves with response, or reject if something went wrong
     */
    return function request(options) {

        // reconfigure pool:
        if (options.sequential) {
            tokenHandler.setMaxSize(1);
        }

        if (_.isEmpty(options.url)) {
            return Promise.reject(new TypeError('At least give a URL...'));
        }

        // Function wrapper which allows the contents to be run now, or added to a queue
        function runRequest() {
            return new Promise(function(resolve, reject){

                // Function wrapper in which the request is actually made
                // Doing this allows a token to be fetched asynchronously before we run it
                function runAjax(customHeaders) {
                    return $.ajax({
                        url: options.url,
                        type: options.method || 'GET',
                        dataType: 'json',
                        headers: customHeaders,
                        data : options.data,
                        async : true,
                        timeout : options.timeout * 1000 || context.timeout * 1000 || 0,
                        contentType : options.contentType || false,
                        beforeSend: function() {
                            console.log('sending header token', customHeaders && customHeaders['X-CSRF-Token']);
                        },
                        global : !options.background//TODO fix this with TT-260
                    })
                    .done(function(response, status, xhr){
                        console.log('received X-CSRF-Token header', xhr.getResponseHeader('X-CSRF-Token'));
                        console.log('received response.token', response.token);

                        // FIXME: check header token, response token,
                        // if no token, use a fake temporary token
                        // ...until server can provide one:
                        var token = xhr.getResponseHeader('X-CSRF-Token') || response.token || 'someToken' + ('' + Date.now()).slice(9);

                        // store the response token for the next request
                        // store with client timestamp so we can expire against client time
                        if (token) {
                            tokenHandler.setToken({
                                value: token,
                                receivedAt: Date.now()
                            });
                        }

                        if (xhr.status === 204 || (response && response.errorCode === 204)) {
                            // no content, so resolve with empty data.
                            return resolve();
                        }

                        // handle case where token expired or invalid
                        if (xhr.status === 401 || (response && response.errorCode === 401)) {
                            feedback().error(__('Unauthorised request'));
                            reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status));
                        }

                        if (response && response.success === true) {
                            // there's some data
                            return resolve(response);
                        }

                        //the server has handled the error
                        return reject(createError(response, __('The server has sent an empty response'), xhr.status));
                    })
                    .fail(function(xhr) {
                        var response;
                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch(parseErr) {
                            response = xhr.responseText;
                        }

                        return reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status));
                    });
                }

                if (!options.noToken) {
                    // we must get a token from the store
                    tokenHandler.getToken()
                        .then(function(token) {
                            var customHeaders;
                            if (token && token.value) {
                                customHeaders = _.extend({}, options.headers, {
                                    'X-Requested-With': 'XMLHttpRequest',  // already present in jQuery.ajax?
                                    'X-CSRF-Token': token.value || 'none', // new key to use globally
                                    'X-Auth-Token': token.value || 'none'  // old key for current TR only
                                });
                            }
                            else {
                                customHeaders = _.extend({}, options.headers);
                            }
                            return customHeaders;
                        })
                        .then(function(customHeaders) {
                            runAjax(customHeaders);
                        });
                }
                else {
                    runAjax(options.headers);
                }
            });
        }

        if (options.noToken === true) {
            //no token protection, run the request
            return runRequest();
        }
        else if (tokenHandler.getQueueLength() === 1) {
            // limited tokens, sequential queue must be used
            return queue.serie(runRequest);
        }
        else {
            // tokens ready
            return runRequest();
        }
    };
});
