/**
 * Copyright (c) 2014, MasterCard International Incorporated
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of
 * conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its
 * contributors may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

/**
 * JQuery validations
 */
(function() {

    isNumeric = function(num) {
        return /[\d\s]/.test(num)
    };

    isTextSelected = function(input) {
        if (typeof input.prop('selectionStart') == "number") {
            return input.prop('selectionStart') != input.prop('selectionEnd');
        } else if (typeof document.selection != "undefined") {
            input.focus();
            return document.selection.createRange().text == input.val();
        }
    };

    cardType = function(num) {

        var MASTERCARD = '51,52,53,54,55,';
        var VISA = '4';
        var AMEX = '34,37,';
        var DISCOVER = '60,62,64,65,';

        if (!num) return null;

        if (num.substring(0, 1) === VISA) {
            return 'visa';
        }
        var prefix = num.substring(0, 2) + ','
        if (prefix.length != 3) return null;

        if (MASTERCARD.indexOf(prefix) != -1) {
            return 'mastercard';
        }
        if (AMEX.indexOf(prefix) != -1) {
            return 'amex';
        }
        if (DISCOVER.indexOf(prefix) != -1) {
            return 'discover';
        }
        return null;
    };

    restrictNumeric = function(e) {

        var keyCode = e.which;
        if (keyCode === 32) {
            return false;
        }
        // allow some special characters, backspace etc
        if (keyCode < 33) {
            return true;
        }

        var keyChar = String.fromCharCode(keyCode);
        return isNumeric(keyChar);
    };

    maxlength = function(e) {

        var input = $(this);

        if (isTextSelected(input)) return;

        var type = input.cardType();
        var keyChar = String.fromCharCode(e.which);

        if (!isNumeric(keyChar)) {
            return;
        }

        var value = input.val() + keyChar;
        value = value.replace(/\D/g, '');

        if (type == 'amex') {
            return value.length <= 15;
        } else {
            return value.length <= 16;
        }
    };

    formatCardInput = function(e) {

        var input = $(this);

        if (isTextSelected(input)) return;

        var type = input.cardType();
        var value = input.val();
        var keyChar = String.fromCharCode(e.which);

        if (!isNumeric(keyChar)) {
            return;
        }

        var maxlength = 16;
        var pattern = /(?:^|\s)(\d{4})$/;

        if (type === 'amex') {
            maxlength = 15;
            pattern = /^(\d{4}|\d{4}\s\d{6})$/;
        }

        var length = (value.replace(/\D/g, '') + keyChar).length;
        if (length >= maxlength) {
            return;
        }

        if (pattern.test(value)) {
            e.preventDefault();
            return input.val(value + ' ' + keyChar);
        } else if (pattern.test(value + keyChar)) {
            e.preventDefault();
            return input.val(value + keyChar + ' ');
        }
    };

    formatCardBackspace = function(e) {

        var BACK_SPACE = 8;

        var input = $(this);
        var value = input.val();

        if (isTextSelected(input)) return;

        if (e.which === BACK_SPACE && /\s\d?$/.test(value)) {
            e.preventDefault();
            return input.val(value.replace(/\s\d?$/, ''));
        }
    };

    formatExpiryInput = function(e) {

        var input = $(this);
        var value = $(this).val();

        if (isTextSelected(input)) return;

        var keyChar = String.fromCharCode(e.which);
        var slash = (keyChar == '/');

        if (value.replace(/\D/g, '').length >= 4 && e.which != 8 && e.which != 0) {
            return false;
        }

        if (!isNumeric(keyChar) && !slash) {
            return;
        }

        if (value.length != 1 && slash) {
            return;
        }

        if (value.length == 1 && slash) {
            input.val('0' + value + '/');
        } else if (value.length == 1 && !slash) {
            input.val(value + keyChar + '/');
        } else {
            input.val(value + keyChar);
        }

        e.preventDefault();
    };

    expiryDate = function(expiry) {

        if (expiry) {

            var dates = expiry.split('/')
            return {
                'month': dates[0],
                'year': dates[1]
            }
        }
        return {
            'month': null,
            'year': null
        }
    };

    /**
     * Restrict the entry of a non numeric in an input field
     */
    $.fn.restrictNumeric = function() {
        return this.keypress(restrictNumeric);
    };

    $.fn.cardType = function() {
        return cardType(this.val());
    };

    /**
     * Formats the entry of a card number into groups of four e.g. **** **** **** ****
     * @returns {*}
     */
    $.fn.formatCardNumber = function() {
        this.restrictNumeric();
        this.keypress(maxlength);
        this.keypress(formatCardInput);
        this.keydown(formatCardBackspace);
        return
    };

    /**
     * Formats an expiry date into dd/dd
     */
    $.fn.formatExpiryNumber = function() {
        this.restrictNumeric();
        this.keypress(formatExpiryInput);
        return
    };

    /**
     * Return an object with the expiry month and year
     *
     * {'month': '01', 'year': '12'}
     *
     * @returns {*}
     */
    $.fn.expiryDate = function() {
        return expiryDate(this.val());
    };

}).call(this);
