/**
 * Copyright © Webscale. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, $t, alert) {
    'use strict';

    $.widget('mage.varnishSyncAccount', {
        options: {
            url: '',
            elementId: '',
            apiKeyValue: '',
            alertTitle: '',
            alertContent: '',
            accounts: '',
            applications: '',
            environments: '',
            selectedAccount: '',
            selectedApplication: '',
            selectedEnvironment: '',
            accountsSelectId: '',
            applicationsSelectId: '',
            environmentsSelectId: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._init, this)
            });
        },

        /**
         * Validate if action can be performed
         * @returns void
         * @private
         */
        _init: function () {
            let self = this;

            self.element.click(function () {
                if (!self.options.apiKeyValue) {
                    alert({
                        title: self.options.alertTitle,
                        content: self.options.alertContent
                    });

                    return false;
                } else {
                    $("body").trigger('processStart');
                    return document.location.href = self.options.url
                }
            });

            this.setAccounts();
        },

        setAccounts: function() {
            let self = this,
                count = 0,
                accounts = JSON.parse(this.options.accounts),
                accountSelect = $('#' + this.options.accountsSelectId);

            for (let account in accounts) {
                count++;

                let selected = (accounts[account].id == self.options.selectedAccount ? ' selected="selected"' : '');
                let option = '<option value="' + accounts[account].id + '"' + selected + '>'
                    + accounts[account].account_name
                    + '</option>';
                accountSelect.append($(option));
            }

            if (count > 0) {
                accountSelect.removeAttr('readonly');
                accountSelect.on('change', function(){
                    var optionSelected = $("option:selected", this).val();
                    self.clearSelects();
                    self.options.selectedApplication = null;
                    self.options.selectedEnvironment = null;
                    self.setApplications(optionSelected);
                });

                if (self.options.selectedAccount) {
                    self.setApplications(self.options.selectedAccount);
                }
            }
        },

        setApplications: function(accountId = null) {
            let self = this,
                applications = JSON.parse(self.options.applications),
                applicationSelect = $('#' + self.options.applicationsSelectId),
                count = 0;

            applicationSelect.attr('readonly', 1);
            self.clearSelects();
            if (accountId !== null) {
                for (let acc in applications) {
                    let account = applications[acc];

                    if (acc == accountId) {
                        for (let app in account) {
                            if (account[app].id != null) {
                                count++;
                                let application = account[app];

                                applicationSelect.append(
                                    $('<option value="' + application.id + '"'
                                        + (application.id == self.options.selectedApplication ? ' selected="selected"' : '')
                                        + '>'
                                        + application.application_name
                                        + '</option>')
                                );
                            }
                        }
                    }
                }

                if (count > 0) {
                    applicationSelect.removeAttr('readonly');
                    applicationSelect.on('change', function(){
                        var optionSelected = $("option:selected", this).val();
                        self.setEnvironments(optionSelected);
                    });

                    if (self.options.selectedApplication) {
                        self.setEnvironments(self.options.selectedApplication);
                    }
                }
            }
        },

        setEnvironments: function(applicationId = null) {
            let self = this,
                environments = JSON.parse(self.options.environments),
                environmentSelect = $('#' + self.options.environmentsSelectId),
                count = 0;

            environmentSelect.attr('readonly', 1);
            environmentSelect.html($('<option>Please Select...</option>'));

            if (applicationId !== null) {
                for (let app in environments) {
                    let application = environments[app];

                    if (app == applicationId) {
                        for (let env in application) {
                            if (application[env].id != null) {
                                count++;
                                let environment = application[env];

                                environmentSelect.append(
                                    $('<option value="' + environment.environment_name + '"'
                                        + (environment.environment_name == self.options.selectedEnvironment ? ' selected="selected"' : '')
                                        + '>'
                                        + environment.environment_name
                                        + '</option>')
                                );
                            }
                        }
                    }
                }

                if (count > 0) {
                    environmentSelect.removeAttr('readonly');
                }
            }
        },

        clearSelects: function() {
            $('#' + this.options.applicationsSelectId)
                .html($('<option>Please Select...</option>'))
                .attr('readonly', 1);
            $('#' + this.options.environmentsSelectId)
                .html($('<option>Please Select...</option>'))
                .attr('readonly', 1);
        }
    });

    return $.mage.varnishSyncAccount;
});
