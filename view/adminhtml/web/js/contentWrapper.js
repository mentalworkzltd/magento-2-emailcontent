define([
    "jquery",
    "mage/translate",
    "jquery/ui",
], function ($, $t) {
    'use strict';

    $.widget('mwz.contentWrapper', {
        options: {
            default_content_wrapper: '',
            content_wrapper: '',
            field_id: '#emailcontent_tablewrapper',
            content_wrapper_field: 'input[name="content_wrapper"]',
        },

        _create: function (data) {
            let _self = this;
            try{
               _self._validateConfig();
               _self._initEvents();
            } catch (e) {
                _self._errorMessageManager(e);
                return console.error(e);
            }
        },

        /**
         * Validate
         * @private
         */
        _validateConfig: function () {
            if(this.options.content_wrapper) {
                this.options.content_wrapper = JSON.parse(this.options.content_wrapper);
                if(!this._compareContentWrapperToDefault()) {

                    // Set custom wrapper form element values
                    $(this.options.field_id + ' li.custom select[name="wrapper"]').val(this.options.content_wrapper.wrapper);
                    $(this.options.field_id + ' li.custom input[name="padding"]').val(this.options.content_wrapper.padding);
                    $(this.options.field_id + ' li.custom input[name="maxwidth"]').val(this.options.content_wrapper.maxwidth);

                    //Uncheck the default, show custom values
                    $(this.options.field_id + ' input[name="default_wrapper"]').prop( "checked", false );
                    $(this.options.field_id + ' li.default, ' + this.options.field_id + ' li.custom').toggle();

                    // Update main form field
                    this._updateContentWrapperFormField(this.options.content_wrapper);
                }
            }
        },

        _initEvents: function () {
            let _self = this;

            $(_self.options.field_id + ' input[name="default_wrapper"]').on('change', function () {
                $(_self.options.field_id + ' li.default, ' + _self.options.field_id + ' li.custom').toggle();
                if( $(_self.options.field_id + ' li.default').is(':hidden')){
                    if(!_self._compareContentWrapperToDefault()) {
                        _self._updateContentWrapperFormField(_self.options.content_wrapper);
                    }
                }else{
                    _self._updateContentWrapperFormField();
                }
            });

            $(this.options.field_id + ' li.custom select, ' + this.options.field_id + ' li.custom input').on('blur', function () {
                let elementName = $(this).attr('name');
                _self.options.content_wrapper[elementName] = $(this).val();
                let contentWrapperData = !_self._compareContentWrapperToDefault() ? _self.options.content_wrapper : '';
                _self._updateContentWrapperFormField(contentWrapperData);
            });
        },

        _updateContentWrapperFormField: function (contentWrapperData) {
            let jsonData = contentWrapperData ? JSON.stringify(contentWrapperData) : '';
            $(this.options.field_id + ' ' + this.options.content_wrapper_field).val(jsonData);
        },

        /**
         *
         * @returns {boolean}
         * @private
         */
        _compareContentWrapperToDefault: function () {
            return (
                this.options.default_content_wrapper &&
		this.options.content_wrapper &&
                (
                    this.options.default_content_wrapper['wrapper'] == this.options.content_wrapper['wrapper'] &&
                    this.options.default_content_wrapper['padding'] == this.options.content_wrapper['padding'] &&
                    this.options.default_content_wrapper['maxwidth'] == this.options.content_wrapper['maxwidth']
                )
            )
        }

    });

    return $.mwz.contentWrapper;

});