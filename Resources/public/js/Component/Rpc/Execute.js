/*global define */
define(
    'ICBaseRpc/Component/Rpc/Execute',
    [
        'jquery',
        'Bisna/Event/Target'
    ],
    function (
        $,
        EventTarget
    ) {
        "use strict";

        /**
         * Execute an RPC call, using dynamic arguments
         *
         * @param configuration object containing rpc values
         *
         * @return RPC
         */
        var RPC = function (configuration) {
            EventTarget.call(this);

            // Set default configuration
            this.configuration = this.getDefaultConfiguration();

            // Merge default configuration with parameter configuration
            this.setConfiguration(configuration);
        };

        $.extend(RPC.prototype, EventTarget.prototype, {
            /**
             * Return a default set of values / properties
             */
            getDefaultConfiguration: function () {
                return {
                    'service':       '',
                    'arguments':     {},
                    'url':           '/rpc/v1/execute',
                    'contentType':   'application/json',
                    'dataType':      'json',
                    'type':          'POST',
                    'beforeSend':    function (xhr, settings) {},
                    'success':       function (data, status, xhr) {},
                    'error':         function (response, status, error) {},
                    'complete':      function (results) {}
                };
            },

            /**
             * Return the current configuration
             */
            getConfiguration: function () {
                return this.configuration;
            },

            /**
             * Merge new configuration object with default (or the most recent) configuration
             *
             * @param object configuration
             */
            setConfiguration: function (configuration) {
                $.extend(this.configuration, configuration);
            },

            /**
             * Generic resource request, via RPC
             */
            execute: function () {
                if (!this.configuration.service) {
                    this.dispatchEvent('execute.error', {
                        'message': 'Invalid or empty service'
                    });

                    return;
                }

                $.ajax(this.configuration.url, {
                    contentType: this.configuration.contentType,
                    dataType: this.configuration.dataType,
                    data: JSON.stringify(this.getPostData()), //this.getPostData
                    type: this.configuration.type,
                    beforeSend: $.proxy(function () {
                        this.dispatchEvent('execute.load.start');

                        this.configuration.beforeSend();
                    }, this),
                    complete: $.proxy(function (response, status) {
                        this.dispatchEvent('execute.load.end');

                        this.configuration.complete(response);
                    }, this),
                    error: $.proxy(function (response, status, error) {
                        var data = JSON.parse(response.responseText)[0];

                        this.dispatchEvent('execute.error', {
                            'class':   data['class'],
                            'message': data.message,
                            'error':   error
                        });

                        this.configuration.error(response, status, error);
                    }, this),
                    success: $.proxy(function (data, status, response) {
                        this.dispatchEvent('execute.success');

                        this.configuration.success(data, status, response);
                    }, this)
                });
            },

            /**
             * Constructs data to be posted on execute (includes 'arguments' and 'service')
             */
            getPostData: function () {
                return {
                    'service': this.configuration.service,
                    'arguments': this.configuration['arguments']
                };
            },

            /**
             * Updates the argument object to be posted on execute (POSTDATA)
             *
             * @param object argumentList
             */
            setArguments: function (argumentList) {
                return $.extend(this.configuration['arguments'], argumentList);
            }
        });

        return RPC;
    }
);
