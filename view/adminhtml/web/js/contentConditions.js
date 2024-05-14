define([
    "jquery",
    'mage/url',
    "mage/translate",
    "jquery/ui",
], function ($, urlBuilder, $t) {
    'use strict';

    $.widget('mwz.contentConditions', {
        element_valid_operators: {
            boolean: ['==','!='],
            select: ['()','!()'],
            input_text: ['==','!=','{}','!{}','()','!()'],
            input_number: ['==','!=','>=','<=','>','<','()','!()'],
            date: ['==','!=','>=','<=','>','<']
        },
        conditions: {
            date: [
                {
                    id: 'range',
                    label: $t('Date Range'),
                    html:  '{{input date data-validation:required name:from_date }} <span>' + $t('to') + '</span> {{input date data-validation:required name:to_date }}',
                }
            ],
            customer: [
                {
                    id: 'id',
                    label: $t('Customer ID'),
                    html:  '{{select operators}}{{input text data-validation:int name:customer_id maxlength:20 placeholder:"' + $t('Enter number') + '"}}',
                },
                {
                    id: 'group',
                    label: $t('Customer Group'),
                    html:   '{{select operators}}{{select customer_group}}'
                }
            ],
            order: [
                {
                    id: 'total-items-qty',
                    label: $t('Order Total Items Qty'),
                    html:   '{{select operators}}{{input text data-validation:int name:order_total-items-qty maxlength:20 placeholder:"' + $t('Enter number') + '"}}'
                },
                {
                    id: 'total-items-weight',
                    label: 'Order Total Items Weight',
                    html:   '{{select operators}}{{input text data-validation:decimal name:order_total-items-weight maxlength:20 placeholder:"' + $t('Enter number') + '"}}'
                },
                {
                    id: 'subtotal-excl-tax',
                    label: $t('Order Subtotal(Excl. Tax)'),
                    html:  '<label>' + $t('Order Subtotal(Excl. Tax)') + '</label>{{select operators}}{{input text data-validation:decimal name:order_subtotal-excl-tax maxlength:20 placeholder:"' + $t('Enter number') + '"}}'
                },
                {
                    id: 'subtotal',
                    label: $t('Order Subtotal'),
                    html:   '{{select operators}}{{input text data-validation:decimal name:order_subtotal maxlength:20 placeholder:"' + $t('Enter number') + '"}}'
                },
                {
                    id: 'total-excl-tax',
                    label: $t('Order Total(Excl. Tax)'),
                    html:  '{{select operators}}{{input text data-validation:decimal name:order_total-excl-tax maxlength:20 placeholder:"' + $t('Enter number') + '"}}'
                },
                {
                    id: 'total',
                    label: $t('Order Total'),
                    html:  '{{select operators}}{{input text data-validation:decimal name:order_total maxlength:20 placeholder:"' + $t('Enter number') + '"}}'
                },
                {
                    id: 'shipping-method',
                    label: $t('Shipping Method'),
                    html:  '{{select operators}}{{select shipping_method}}'
                }
            ],
            orderitem: [
                {
                    id: 'attribute',
                    label: $t('Order Item Attribute Combination'),
                    html:   '{{select product_attribute}}<span class="dynamic_content">...</span>'
                },
                {
                    id: 'attribute-set',
                    label: $t('Order Item Product Attribute Set'),
                    html:   '{{select operators}}{{select orderitem_attribute-set}}'
                },
                {
                    id: 'category',
                    label: $t('Order Item Product Category'),
                    html:   '{{select operators}}{{select orderitem_category}}'
                },
            ],
        },
        options: {
            product_attribute_ajaxurl: urlBuilder.build("/admin/emailcontent/ajax/getattributeoptions"),
            display_conditions_field: 'input[name="display_conditions"]',
            field_id: '#emailcontent_conditions',
            conditions_select_id: 'select[name="conditions_list"]',
            condition_config: {
                active_display_conditions: [],
                select_options: {},
                operators: {
                    '==': $t('is'),
                    '!=': $t('is not'),
                    '>=': $t('equals or greater than'),
                    '>': $t('greater than'),
                    '<': $t('less than'),
                    '{}': $t('contains'),
                    '!{}': $t('does not contain'),
                    '()': $t('is one of'),
                    '!()': $t('is not one of')
                }
            }
        },

        _create: function (data) {
            let _self = this;
            try{
                _self._validateConfig();
                _self._initTemplateField();
                _self._populateActiveConditions();
            } catch (e) {
                _self._errorMessageManager(e);
                return console.error(e);
            }
        },

        /**
         * Validate the condition_config passed to widget before init the display_conditions field
         * @private
         */
        _validateConfig: function () {

            if(!this.options.condition_config.operators ||
                (
                    !this.options.condition_config.select_options.customer_group &&
                    !this.options.condition_config.select_options.product_attribute_set &&
                    !this.options.condition_config.select_options.product_category &&
                    !this.options.condition_config.select_options.shipping_method &&
                    !this.options.condition_config.select_options.product_attribute
                )
            ){
                throw new Error($t('Invalid config data found. Can not initialise conditions field.'));
            }

            if(this.options.condition_config.active_display_conditions.length) {
                $(this.options.field_id + ' ' + this.options.display_conditions_field).val(this.options.condition_config.active_display_conditions);
            }
        },

        /**
         * initialise the main display_conditions field
         * @private
         */
        _initTemplateField: function () {
            let _self = this;

            // Build conditions_list select based on available conditions config
            $.each(_self.conditions, function (key, condition) {
                let $optgroup = $("<optgroup label='" + _self._capitalize(key) + "'>");
                for (let i=0; i<condition.length; i++) {
                    let option = "<option value='" + key + '_' + condition[i].id + "'>" + condition[i].label + "</option>";
                    $optgroup.append(option);
                }
                $(_self.options.conditions_select_id).append($optgroup);
            });

            // Build operator select, will be copied/modified as condition selector rows are inserted
            let $operatorSelect = $('<select />').attr('name', 'operator').addClass('operator_template').attr('style', 'display:none;');
            $.each( this.options.condition_config.operators, function( key, value ) {
                $operatorSelect.append("<option value='" + key + "'>" + value + "</option>");
            });
            $(_self.options.field_id).prepend($operatorSelect);

            // Initialise common events
            _self._initEvents();

            // Append/configure conditions selector row clone for use
            _self._renderConditionSelectorClone();
        },

        /**
         * Common widget events
         * @private
         */
        _initEvents: function (){
            let _self = this;

            // Scope condition - all/any|true/false
            $('select[name="scope_1"], select[name="scope_2"]', _self.options.field_id).on('change', function (){
                _self._updateDisplayConditionsFormFieldValue();
            });

            // Remove condition row event
            $(_self.options.field_id + ' a.remove_condition').on('click', function () {
                $(this).parent().remove();
                _self._updateDisplayConditionsFormFieldValue();
            });

            // Choose condition event
            $(_self.options.field_id + ' .choose_condition').on('click', function () {

                let isSubcondition = false;
                let parentElement = $(this).closest('li');
                let checkConditionsSelector = $(_self.options.field_id + ' ul.conditions li:not(#conditions_template)');

                if($(this).closest('.subconditionchooser').length){
                    isSubcondition = true;
                    parentElement =  $(this).closest('.condition');
                    checkConditionsSelector = $('.subcondition:last', parentElement);
                }

                //check previous condition rows are valid before allow further condition rows
                let allowConditionRow = true;
                $.each($(checkConditionsSelector), function (index, object) {
                        if (allowConditionRow && $('.value', object).length) {
                            $.each($('.value', object), function () {
                                if (allowConditionRow && _self._isEmpty($(this).val())) {
                                    allowConditionRow = false;
                                    $(this).addClass('error').trigger('focus');
                                    _self._addValidationErrorMessage(parentElement, $t('Please enter a value'));
                                }
                            });
                        }
                });

                if (allowConditionRow) {
                    if(isSubcondition){
                        let conditionHtml = _self._getConditionById('orderitem_attribute');
                        _self._renderSubConditionHtml(conditionHtml, [], parentElement);
                    }else{
                        $(_self.options.conditions_select_id, parentElement).show().trigger('focus');
                        $(this).hide();
                    }
                }

            });

            // Manage condition selector select events
            $(_self.options.conditions_select_id).on('blur', function (){
                $(this).hide();
                let parentElement = $(this).closest('li');
                if($('.condition', parentElement).html().length === 0){
                    $('.choose_condition', parentElement).show();
                }
            });
            $(_self.options.conditions_select_id).on('change', function () {
                if($(this).val()){
                    let parentElement = $(this).closest('li');
                    _self._renderConditionHtml($(this).val(), parentElement);
                    $('.condition .value:first', parentElement).trigger('focus');
                    $(this).remove();
                }
            });
        },

        /**
         * Manage main UI form display_conditions field save data
         *
         * @private
         */
        _updateDisplayConditionsFormFieldValue: function () {
            let conditions = [];
            $.each($(this.options.field_id + ' li:not(#conditions_template)') , function (index, object) {
                if($(this).data('id')) {
                    let conditionValues = {
                        id: $(this).data('id'),
                        values: []
                    };

                    if($(this).data('id') === 'orderitem_attribute'){
                        $.each($('.condition .subcondition',this), function (){
                            let subconditionValues = [];
                            $.each($('select, input', this), function (index, object) {
                                let value = {
                                    name: $(object).attr('name'),
                                    value: $(object).val()
                                };
                                subconditionValues.push(value);
                            });
                            if(subconditionValues){
                                conditionValues.values.push(subconditionValues);
                            }
                        });
                    }else{
                        $.each($('select, input', this).not('[name="conditions_list"]'), function (index, object) {
                            let value = {
                                name: $(object).attr('name'),
                                value: $(object).val()
                            };
                            conditionValues.values.push(value);
                        });
                    }

                    if(conditionValues) {
                        conditions.push(conditionValues);
                    }
                }
            });

            if(conditions.length){ // Add condition scope data - all|any true|false
                conditions.push({
                    id: 'scope',
                    values: [
                        { name: 'scope_1', value: $(this.options.field_id + ' select[name="scope_1"]').val()},
                        { name: 'scope_2', value: $(this.options.field_id + ' select[name="scope_2"]').val()}
                    ]
                });
            }

            //console.log('conditions', conditions);
            $(this.options.field_id + ' ' + this.options.display_conditions_field).val(JSON.stringify(conditions));

        },

        /**
         * Populate any saved model or persisted conditions
         *
         * @private
         */
        _populateActiveConditions: function () {
            let _self = this;

            if(_self.options.condition_config.active_display_conditions){
                let display_conditions = JSON.parse(_self.options.condition_config.active_display_conditions);
                $.each(display_conditions, function (index, condition) {
                    if(condition.id === 'scope'){
                        _self._updateConditionScope(condition.values);
                    }else{
                        _self._renderConditionHtml(condition.id, $(_self.options.field_id + ' ul.conditions li').eq(-1), condition.values);
                    }
                });
            }
        },

        /**
         * Render condition selector rows as needed
         * @private
         */
        _renderConditionSelectorClone: function () {
            let $liClone = $('li#conditions_template').clone(true).attr('style', '').attr('id', '');
            $(this.options.field_id + ' ul.conditions').append($liClone);
        },

        /**
         * Manage the conditions scope fields - all|any true|false
         * @private
         */
        _updateConditionScope: function (values) {
            let _self = this;
            $.each(values, function (index, value) {
                let $scopeElement = $('select[name="' + value.name + '"]', _self.options.field_id);
                if($scopeElement.length){
                    $scopeElement.val(value.value);
                }
            });
        },

        /**
         * Configure the condition HTML, adding any form elements, and the validation of those elements
         * @param value
         * @param parentElement
         * @private
         */
        _renderConditionHtml: function (condition_id, parentElement, conditionValues) {
            let _self = this;

            try {
                let condition = _self._getConditionById(condition_id);

                // Add label
                condition.html = '<label>' + condition.label + '</label>' + condition.html;

                // Add form elements
                if(condition.html.indexOf('{{') !== -1){
                    let elementSubstrings = [];
                    let conditionHtmlSubstring = condition.html;
                    while (conditionHtmlSubstring.indexOf('{{') > -1) {
                        let elementSubstring = conditionHtmlSubstring.substring(
                            conditionHtmlSubstring.indexOf('{{') + 2,
                            conditionHtmlSubstring.indexOf("}}")
                        );
                        if(elementSubstring.indexOf('operators') === -1) {// ignore operator select, we process that after
                            elementSubstrings.push(elementSubstring);
                        }
                        conditionHtmlSubstring = conditionHtmlSubstring.replace('{{' + elementSubstring + '}}', '');
                    }

                    // If populating a saved condition, does it have an operator value
                    let conditionOperatorObject = '';
                    if(conditionValues){
                        if(condition_id === 'orderitem_attribute'){
                            conditionOperatorObject = conditionValues[0].find((value) => value.name === "operator");
                        }else{
                            conditionOperatorObject = conditionValues.find((value) => value.name === "operator");
                        }
                    }
                    let conditionOperator = conditionOperatorObject ? conditionOperatorObject.value : '';

                    let validOperators = [];
                    let index = 0;
                    $.each(elementSubstrings, function (index, elementSubstring){
                        let elementHtml = '';
                        if(elementSubstring.indexOf('select ') > -1){
                            let useConditionValues = (condition_id === 'orderitem_attribute' && conditionValues) ? conditionValues[0] : conditionValues;
                            elementHtml = _self._getSelectHtml(elementSubstring.replace('select ', ''), useConditionValues, 1);
                            validOperators = _self.element_valid_operators.select;
                        }else if(elementSubstring.indexOf('input ') > -1){
                            elementHtml = _self._getInputHtml(elementSubstring.replace('input ', ''), conditionValues);
                            validOperators = (elementSubstring.indexOf('date') > -1) ? _self.element_valid_operators.date :
                                _self.element_valid_operators.input_number;
                        }
                        condition.html = condition.html.replace('{{' + elementSubstring + '}}', elementHtml);
                        index++;
                    });

                    // Add operator select
                    if(condition.html.indexOf('{{select operators}}') !== -1){
                        let $operatorSelectHtml = this._getOperatorSelectHtml(validOperators, conditionOperator);
                        let index = $(parentElement).parent().children().index(parentElement);
                        condition.html = condition.html.replace('{{select operators}}', $operatorSelectHtml);
                    }
                }

                // Add condition ID
                $(parentElement).attr('data-id', condition_id);

                // If orderitem_attribute, there can be multiple sub-conditions added,
                // and sub-condition operator and input value are dynamic based on selection
                if(condition_id === 'orderitem_attribute'){
                    if(conditionValues){
                        $.each(conditionValues, function (index, conditionValue) {
                            _self._renderSubConditionHtml(condition.html, conditionValue, $('.condition ', parentElement));
                        });
                    }else{
                        _self._renderSubConditionHtml(condition.html, [], $('.condition ', parentElement));
                    }
                }else{
                    // Add condition HTML
                    $('.condition', parentElement).html(condition.html);
                }

                // Finalise the condition HTMl rendering
                $('> .remove_condition', parentElement).show();
                $('> .choose_condition', parentElement).hide();

                // Manage validation for condition user input fields
                this._manageConditionValidation();

            }catch(e){
                console.log(e);
                this._resetConditionRow(parentElement);
                return;
            }

            // Add next selector row
            this._renderConditionSelectorClone();
        },

        /**
         * Render sub-condition HTML for orderitem_attribute condition
         *
         * @param conditionHtml
         * @param conditionValues
         * @param parentElement
         * @private
         */
        _renderSubConditionHtml: function (conditionHtml, conditionValues, parentElement) {

            // Setup the subcondition area - we are populating with saved subconditions
            let hasSubconditions = $('.subcondition', parentElement).length;
            if(!hasSubconditions){
                let subConditionChoose = $('li#conditions_template .choose_condition').clone(true).attr('style', '');
                let subConditionChooserHtml = $('<div />').addClass('subconditionchooser').prop("outerHTML");
                $(parentElement).append(subConditionChooserHtml);
                $('.subconditionchooser', parentElement).append(subConditionChoose);
            }

            // Add the subcondition html
            let subConditionHtml = $('<div />').addClass('subcondition').html(conditionHtml);

            // If already has subconditions...we are adding another
            if(hasSubconditions) {
                let subconditionClone = $('.subcondition:last', parentElement).clone();
                $('.dynamic_content', subconditionClone).html('&nbsp;...');
                $(subConditionHtml).html(subconditionClone.html());

                // Second subconditions onwards already have this element
                if(!$('.remove_condition', subconditionClone).length){
                    let subConditionRemove = $('li#conditions_template .remove_condition').clone(true).attr('style', '');
                    $(subConditionHtml).append(subConditionRemove);
                }

                let selectedCondition = (conditionValues) ? conditionValues.find((value) => value.name.indexOf('::') > -1) : null;
                let selectVal = selectedCondition ? selectedCondition.name : '';
                $('select', subConditionHtml).val(selectVal).data('oldvalue', selectVal);

                console.log('conditionValues', conditionValues);

            }

            $('.subconditionchooser', parentElement).before(subConditionHtml);

            let subconditionParent = $('.subcondition:last', parentElement);
            $('.value:first', subconditionParent).trigger('focus');
            this._manageOrderItemAttributeCondition(conditionValues, subconditionParent);
            this._manageConditionValidation();
        },

        /**
         * Product attributes require input fields creating dynamically based on input/type
         * Select/multiselect options are gathered via ajax
         *
         * @param parentElement
         * @private
         */
        _manageOrderItemAttributeCondition: function (conditionValues, parentElement) {
            let _self = this;
            if(conditionValues) {
                conditionValues.shift();// Do not need first value, already selected
            }

            $('select[name="product_attribute"]', parentElement).on('change', function (){
                $('.dynamic_content', parentElement).html('&nbsp;...');
                if($(this).val()){
                    let $selectedOption = $('option:selected', this);
                    if($.inArray($selectedOption.data('input'), [ "multiselect", "select"] ) > -1){
                        let ajaxData = {
                            attribute_code: $selectedOption.val()
                        };
                        _self._ajaxGetProductAttributeOptions(_self.options.product_attribute_ajaxurl, ajaxData, conditionValues, parentElement);
                    }else{
                        let validOperators = [];
                        let attributeHtml = '';
                        switch($selectedOption.data('input')){
                            case 'boolean':
                                attributeHtml = _self._getSelectHtml('boolean');
                                validOperators = _self.element_valid_operators.boolean;
                                break;
                            default: // input, date
                                validOperators = ($.inArray($selectedOption.data('type'), ['decimal','int']) > -1) ?
                                    _self.element_valid_operators.input_number :
                                    _self.element_valid_operators.input_text;

                                let validation = 'required';
                                let placeholder = 'Enter a value';
                                if($.inArray($selectedOption.data('type'), ['decimal','int']) > -1){
                                    placeholder = $t('Enter a number');
                                    validation = $selectedOption.data('type');
                                }
                                let inputType = $selectedOption.data('input');
                                attributeHtml = _self._getInputHtml(inputType + ' data-validation:' + validation + ' name:' + $(this).val() + ' placeholder:"' + placeholder + '"', conditionValues);
                        }

                        let selectedOperatorCondition = conditionValues ? conditionValues.find((value) => value.name === 'operator') : '';
                        let selectedOperator = selectedOperatorCondition ? selectedOperatorCondition.value : '';
                        let operatorHtml = _self._getOperatorSelectHtml(validOperators, selectedOperator);
                        $('.dynamic_content', parentElement).html(operatorHtml + attributeHtml);
                        _self._manageConditionValidation();
                    }
                }
            });

            if(conditionValues){
                $('select[name="product_attribute"]', parentElement).trigger('change');
            }
        },

        /**
         * Get orderitem attribute condition, attribute options via Ajax
         *
         * @param url
         * @param data
         * @param conditionValues
         * @param parentElement
         * @private
         */
        _ajaxGetProductAttributeOptions: function (url, data, conditionValues, parentElement) {
            let _self = this;

            data.form_key =  FORM_KEY;
            $.ajax({
                type: 'POST',
                showLoader: true,
                dataType: 'json',
                url: url,
                data: data,
                success: function (response) {
                    _self._updateProductAttributeCondition(response, conditionValues, parentElement);
                },
                error: function (request, error) {
                    console.log(error);
                }
            });
        },

        /**
         * Process the response from the ajax call, populate the product attribute select
         *
         * @param response
         * @param parentElement
         * @private
         */
        _updateProductAttributeCondition: function (response, conditionValues, parentElement) {
            if(response.options){
                this._generateSelectHtml(response.attribute_code, response.options, conditionValues, parentElement);
            }else{
                alert('No options returned for selected attribute.');
                $('.dynamic_content', parentElement).html('&nbsp;...');
                $('select[name="product_attribute"]', parentElement).val('').trigger('focus');

            }
        },

        /**
         * Create a select from given options/name
         *
         * @param attribute_code
         * @param options
         * @private
         */
        _generateSelectHtml: function (attribute_code, options, conditionValues, parentElement) {

            let selectedValueCondition = conditionValues ? conditionValues.find((value) => value.name === attribute_code) : '';
            let conditionValue = selectedValueCondition ? selectedValueCondition.value : '';

            let $selectObj = $('<select />').attr('name', attribute_code).attr('multiple', '').addClass('value').attr('data-validation', 'required').attr('data-oldvalue', '');
            $.each(options, function (index, option) {
                let selected = ($.inArray(option.value, conditionValue) > -1) ? 'selected="selected"' : '';
                let optionHtml = "<option " + selected + " value='" + option.value + "'>" + option.label + "</option>";
                $selectObj.append(optionHtml);
            });

            let selectedOperatorCondition = conditionValues ? conditionValues.find((value) => value.name === 'operator') : '';
            let operatorValue = selectedOperatorCondition ? selectedOperatorCondition.value : '';
            let $operatorSelectHtml = this._getOperatorSelectHtml(this.element_valid_operators.select, operatorValue);

            $('.dynamic_content', parentElement).html('').append($operatorSelectHtml).append($selectObj);
            this._manageConditionValidation();
        },

        /**
         * mageInit seems to have no effect on UI form - HTML content fields, and trying to validate
         * single user input condition related fields (using $.validator) throws errors,
         * so we manage the limited validation we need for condition rows ourselves.
         *
         * This allows us to also manage previously selected field values (data-oldvalue) when the user
         * tries to modify previously defined condition selector rows with invalid data.
         */
        _manageConditionValidation: function () {
            let _self = this;

            $.each($(this.options.field_id + ' li:not(#conditions_template) .condition input, ' + this.options.field_id + ' li:not(#conditions_template) .condition select'), function (index, object) {
                $(this).on('focus', function () {
                   if($(this).val()){
                       $(this).removeClass('error');
                   }
                });

                let parentElement = $(this).closest('li');
                let validation = $(this).data('validation');
                switch(validation){
                    case 'decimal':
                    case 'int':
                        $(this).on('blur', function (e) {
                            let isValid = true;

                            let $operatorSelect = $('select[name="operator"]', $(this).closest('li'));
                            if(
                                $operatorSelect &&
                                $.inArray($operatorSelect.val(), _self.element_valid_operators.select) > -1 &&
                                $(this).val().indexOf(',') > -1
                            ){
                                let values = $(this).val().split(',').filter(n => n).map(function(str) { return str.replace(/ /g, ""); });
                                $.each(values, function (index, value){
                                    if(isValid){
                                        if(validation === 'decimal'){
                                            isValid = $.mage.isEmptyNoTrim(value) || !isNaN($.mage.parseNumber(value)) && /^\s*-?\d*(\.\d*)?\s*$/.test(value);
                                        }else{
                                            isValid =  /^-?\d+$/.test(value);
                                        }
                                    }
                                });
                                if(isValid){
                                    $(this).val(values.join(',')).data('oldvalue',values.join(','));
                                }
                            } else {
                                if(validation === 'decimal'){
                                    isValid = $.mage.isEmptyNoTrim($(this).val()) || !isNaN($.mage.parseNumber($(this).val())) && /^\s*-?\d*(\.\d*)?\s*$/.test($(this).val());
                                }else{
                                    isValid =  /^-?\d+$/.test($(this).val());
                                }
                            }

                            if(!isValid){
                                let oldVal = $(this).data('oldvalue');

                                // May have switched from multiple to single value related operator
                                if(validation === 'decimal'){
                                    isValid = $.mage.isEmptyNoTrim(oldVal) || !isNaN($.mage.parseNumber(oldVal)) && /^\s*-?\d*(\.\d*)?\s*$/.test(oldVal);
                                }else{
                                    isValid =  /^-?\d+$/.test(oldVal);
                                }
                                oldVal = isValid ? oldVal : '';

                                $(this).addClass('error').val(oldVal).data('oldvalue', oldVal);
                                _self._addValidationErrorMessage(parentElement, (oldVal) ? $t('Invalid value, reverted to old value') : $t('Please enter a value'));
                            }else{
                                $(this).removeClass('error');
                                $(this).data('oldvalue', $(this).val());
                                _self._updateDisplayConditionsFormFieldValue();
                            }
                        });
                        break;
                    case 'required': // single/multiple select/input element validation
                        $(this).on('blur', function (e) {
                           if(!$(this).val()){
                               let oldVal = $(this).data('oldvalue');
                               $(this).addClass('error').val(oldVal);
                               _self._addValidationErrorMessage(parentElement, (oldVal) ? $t('Invalid value, reverted to old value') : $t('Please enter a value'));
                           }else{
                               $(this).removeClass('error');
                               $(this).data('oldvalue', $(this).val());
                               _self._updateDisplayConditionsFormFieldValue();
                           }
                        });
                        break;
                    default:
                }
            });
        },

        /**
         * Get a modified copy of the operator select element HTML.
         * Remove operator options that are not valid for the current condition selector being rendered.
         * @param validOperators
         * @private
         */
        _getOperatorSelectHtml: function (validOperators, conditionOperator) {

            let $operatorSelectClone = $(this.options.field_id + ' select[name="operator"]').clone();
            $.each( $('option', $operatorSelectClone), function( index, option ) {
                if($.inArray($(option).val(), validOperators) === -1){
                    $('option[value="' + $(option).val() + '"]', $operatorSelectClone).remove();
                }
            });
            if(conditionOperator){
                $('option[value="' + conditionOperator + '"]', $operatorSelectClone).attr('selected', 'selected');
            }
            return $operatorSelectClone.attr('style', '').removeClass('operator_template').prop("outerHTML");
        },

        /**
         * Get condition select HTML, populating select with option data
         * @param selectSubstring
         * @private
         */
        _getSelectHtml: function (selectSubstring, conditionValues, allowMultiple) {

            let _self = this;

            let selectParams = selectSubstring.split(' ');
            if(!selectParams.length){
                throw new Error($t('Select element parameters not found for condition'));
            }

            let conditionValueObj = conditionValues ? conditionValues.find((value) => value.name === selectParams[0]) : '';
            let conditionValue = conditionValueObj ? conditionValueObj.value : '';

            let $selectObj = $('<select />').attr('name', selectParams[0]).addClass('value').attr('data-validation', 'required').attr('data-oldvalue', '');
            if(allowMultiple){
                $selectObj.attr('multiple', '');
            }

            let optionHtml = '';
            $.each(selectParams, function (index, param) {
                if (!index) {
                    if (typeof _self.options.condition_config.select_options[selectParams[0]] !== "undefined") {
                        let $optgroup = null;
                        $.each(_self.options.condition_config.select_options[selectParams[0]], function (index, option) {
                            if(option.value === 'optgroup'){
                                if($optgroup){
                                    $selectObj.append($optgroup);
                                }
                                $optgroup = $("<optgroup label='" + option.label + "'>");
                            }else{
                                let selected = (conditionValue && $.inArray(option.value, conditionValue) > -1) ? 'selected="selected"' : '';
                                let hasInput = typeof option.type !== "undefined" ? 'data-input="' + option.input + '"' : '';
                                let hasType = typeof option.type !== "undefined" ? 'data-type="' + option.type + '"' : '';
                                optionHtml = "<option " + selected + " value='" + option.value + "' " + hasInput + ' ' + hasType + ">" + option.label + "</option>";

                                if($optgroup){
                                    $optgroup.append(optionHtml);
                                }else{
                                    $selectObj.append(optionHtml);
                                }
                            }
                        });
                        if($optgroup){
                            $selectObj.append($optgroup);
                        }
                    }else{
                        throw new Error($t('Select options not found for condition'));
                    }
                }
            });
            $selectObj.attr('data-oldvalue', conditionValue);
            return $selectObj.prop("outerHTML");
        },

        /**
         * Get condition input HTML
         *
         * @param inputSubstring
         * @param inputValue
         * @private
         */
        _getInputHtml: function (inputSubstring, conditionValues) {
            let _self = this;

            let inputParams = inputSubstring.trim().match(/(?:[^\s"]+|"[^"]*")+/g);
            if(!inputParams.length){
                throw new Error($t('Input element parameters not found for condition'));
            }

            let $inputObj = $('<input />', {
                "type" : inputParams[0],
                "class": "value",
                "data-oldvalue": "",
                "value": ""
            });

            $.each(inputParams, function (index, param){
                if(index){
                    let paramParts = param.match(/(?:[^\:]|::)+/g);
                    if(paramParts[0] === 'placeholder'){
                        paramParts[1] = $t(paramParts[1].substring(1, paramParts[1].length - 1));
                    }
                    $inputObj.attr(paramParts[0], paramParts[1]);
                }
            });

            let conditionValueObj = conditionValues ? conditionValues.find((value) => value.name === $inputObj.attr('name')) : '';
            let inputValue = conditionValueObj ? conditionValueObj.value : '';

            $inputObj.attr('value', inputValue).attr('data-oldvalue', inputValue);
            return $inputObj.prop("outerHTML");
        },

        /**
         * If issue initialising a condition selector row, reset it
         *
         * @param rowElement
         * @private
         */
        _resetConditionRow: function (rowElement) {
            $('a.choose_condition', rowElement).show();
            $(this.options.conditions_select_id, rowElement).hide();
            $('div.condition', rowElement).html('');
        },

        /**
         * Condition selector row, user input validation error message handler
         *
         * @param parentElement
         * @param message
         * @private
         */
        _addValidationErrorMessage: function (parentElement, message) {
            let $messageElement = $('<span />').addClass('validation_message').html($t(message));
            $('.condition', parentElement).append($messageElement);
            setTimeout(function () {
                $('span.validation_message', parentElement).fadeOut(1000, function () {
                    $(this).remove();
                });
            }, 3000);
        },

        /**
         * General field error message handler if issue initialising the display_conditions field
         *
         * @param message
         * @private
         */
        _errorMessageManager: function (message) {
            let $messageField = $(this.options.field_id +' p.error-message');
            let $innerWrapperField = $(this.options.field_id +' .inner-wrapper');

            $messageField.html(message)
            if(message){
                $messageField.show();
                $innerWrapperField.hide();
            }else{
                $messageField.hide();
                $innerWrapperField.show();
            }
        },

        /**
         * Get condition by its ID
         *
         * @param conditionId
         * @returns {*|boolean}
         * @private
         */
        _getConditionById: function (conditionId) {

            let conditionIdParts = conditionId.split('_');
            let condition = null;
            $.each(this.conditions[conditionIdParts[0].toLowerCase()], function (index, _condition){
                if(!condition){
                    if(_condition.id === conditionIdParts[1]){
                        condition = Object.assign({},_condition);
                    }
                }
            });
            if(!condition){
                throw new Error($t('Condition does not exist [' + condition_id + ']'));
            }

            return condition;
        },

        /**
         * Capitalize the first letter of a string
         *
         * @param string
         * @returns {string}
         * @private
         */
        _capitalize: function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        /**
         * Check if a value is empty, empty array or array with just empty elements.
         * Helps with multiple select validation
         * @param value
         * @returns {boolean}
         * @private
         */
        _isEmpty: function (value){
            if(Array.isArray(value)){
                let hasValue = false;
                $.each(value, function (index, val) {
                    if(!hasValue && val.length){
                        hasValue = true;
                    }
                });
                return !hasValue;
            }

            return !value
        }

    });

    return $.mwz.contentConditions;

});