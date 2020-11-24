<style type="text/css">
@import url('https://fonts.googleapis.com/css?family=Noto+Sans');
@import url('../../../sass/transitions.scss');

.newsletter-rectangle-preview-close {
    position: absolute;
    display: block;
    top: 0;
    right: 0;
    text-decoration: none;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    min-width: 40px;
    height: 40px;
    letter-spacing: 0.05em;
    line-height: 40px;
    padding-right: 3px;
    text-align: right;
}

a.newsletter-rectangle-preview-close::after {
    content: "\00a0\00d7\00a0";
    font-size: 24px;
    vertical-align: sub;
    font-weight: normal;
    line-height: 40px;
    display: inline-block;
}

.newsletter-rectangle-form {
    position: relative;
    margin: 0 auto;
    padding: 20px;
    font-size: 14px;
    line-height: 20px;
    max-width: 420px;
    overflow: hidden;
}

.newsletter-rectangle-title {
    font-size: 18px;
    line-height: 26px;
    font-weight: bold;
    margin-bottom: 15px;
}

.newsletter-rectangle-text {
    margin-bottom: 15px;
    white-space: pre-line;
}

.newsletter-rectangle-form-label {
    display: none;
}

.newsletter-rectangle-form-inputs {
    margin: 20px 0;
    border: none;
    padding: 0;
}

.newsletter-rectangle-form-input {
    width: 100%;
    height: 50px;
    line-height: 22px;
    border: 0;
    border-radius: 3px;
    font-size: 16px;
    padding: 0 10px;
    color: #000000;
}

.newsletter-rectangle-form-button {
    display: block;
    width: 100%;
    margin-top: 10px;
    border: 0;
    height: 50px;
    line-height: 50px;
    border-radius: 3px;
    font-size: 15px;
}

.newsletter-rectangle-disclaimer {
    font-size: 11px;
    text-align: center;
    margin: 10px 20px;
    font-weight: 300;
    line-height: 16px;
}

.newsletter-rectangle-disclaimer a {
    text-decoration: underline;
    white-space: nowrap;
}

.newsletter-rectangle-failure-message {
    text-align: left;
}

.compact .newsletter-rectangle-title, .compact .newsletter-rectangle-text  {
    margin-bottom: 10px;
}

.compact .newsletter-rectangle-form-inputs {
    margin: 10px 0;
}

.compact .newsletter-rectangle-form-input, .compact .newsletter-rectangle-form-button {
    height: 40px;
}
.compact .newsletter-rectangle-form-button {
    line-height: 40px;
    margin-top: 7px;
}

.compact.newsletter-rectangle-form {
    padding: 15px;
}

.newsletter-rectangle-form-button.newsletter-rectangle-failure {
    color: #b00c28 !important;
}

.newsletter-rectangle-form-button.newsletter-rectangle-doing-ajax::after {
    content: '\2022\2022\2022';
}

</style>

<template>
    <div v-if="isVisible"
         class="newsletter-rectangle-preview"
         v-bind:style="[containerStyles, _position]"
    >
        <transition appear v-bind:name="transition">

            <form class="newsletter-rectangle-form" target="_blank"
                  v-bind:style="[boxStyles]"
                  v-bind:method="requestMethod"
                  v-bind:action="endpoint"
                  v-bind:class="boxClass"
                  v-on:submit="_formSubmit"
            >
                <a class="newsletter-rectangle-preview-close"
                   href="javascript://"
                   v-bind:class="[{hidden: !closeable}]"
                   v-on:click.stop="$parent.closed"
                   v-bind:style="[linkStyles]">{{ closeText }}</a>

                <div class="newsletter-rectangle-title"
                     v-html="$parent.injectVars(title)"></div>
                <div class="newsletter-rectangle-text" v-html="$parent.injectVars(text)"></div>

                <fieldset class="newsletter-rectangle-form-inputs">
                    <label class="newsletter-rectangle-form-label" for="newsletter-rectangle-form-email">Email</label>
                    <input class="newsletter-rectangle-form-input"
                           type="email" required
                           placeholder="e-mail"
                           id="newsletter-rectangle-form-email"
                           @keydown="clearLastResponse"
                           v-bind:class="{'newsletter-rectangle-doing-ajax': doingAjax}"
                           v-bind:disabled="doingAjax"
                           v-bind:name="_form('email')">

                    <input type="hidden" v-bind:name="_form('newsletter_id')" v-bind:value="newsletterId">
                    <input type="hidden" v-bind:name="_form('source')" v-bind:value="_source">
                    <input type="hidden" v-bind:name="_form('referer')" v-bind:value="_referer">
                    <input v-for="(value, name) in paramsExtra" type="hidden" v-bind:name="name" v-bind:value="value">
                    <button class="newsletter-rectangle-form-button"
                           v-bind:disabled="doingAjax || subscriptionSuccess !== null"
                           v-bind:class="{
                               'newsletter-rectangle-doing-ajax': doingAjax,
                               'newsletter-rectangle-failure': subscriptionSuccess === false,
                               'newsletter-rectangle-success': subscriptionSuccess === true }"
                           v-bind:style="[buttonStyles]">{{ _btnSubmit }}</button>
                </fieldset>

                <div class="newsletter-rectangle-failure-message" v-if="subscriptionSuccess === false" v-html="failureMessage"></div>
                <div class="newsletter-rectangle-disclaimer" v-html="_terms" ></div>
            </form>

        </transition>

    </div>

</template>

<script>
export default {
    name: 'newsletter-rectangle-preview',
    props: [
        "alignmentOptions",
        "positionOptions",
        "show",
        "uuid",
        "campaignUuid",
        "forcedPosition",

        "newsletterId",
        "btnSubmit",
        "title",
        "text",
        "success",
        "failure",
        "terms",
        "urlTerms",
        "backgroundColor",
        "buttonBackgroundColor",
        "textColor",
        "buttonTextColor",
        "width",
        "height",

        "endpoint",
        "useXhr",
        "requestMethod",
        "requestBody",
        "requestHeaders",
        "paramsTr",
        "paramsExtra",
        "responseFailure",

        "position",
        "offsetVertical",
        "offsetHorizontal",
        "closeable",
        "closeText",
        "transition",
        "displayType",
    ],
    data: function () {
        return {
            visible: true,
            closeTracked: false,
            clickTracked: false,
            subscriptionSuccess: null,
            doingAjax: false,
            failureMessage: "",
        }
    },
    methods: {
        _form: function (name){
            if (typeof this.paramsTr == 'object' && this.paramsTr.hasOwnProperty(name)){
                return this.paramsTr[name];
            }
            return name;
        },
        _formSubmit: function (event){
            let form = event.target;
            let formData = new FormData(form);
            let headers = new Headers;
            let data;
            let settings = {};
            let request;

            if (!this.useXhr){
                this.$parent.clicked(event, true);
                return true;
            }

            event.preventDefault();
            event.stopPropagation();
            this.$parent.clicked(event,false);

            for (const [key, value] of Object.entries(this.requestHeaders)) {
                headers.set(key, value.toString());
            }

            switch (this.requestBody){
                case 'json':
                    data = JSON.stringify(Object.fromEntries(formData.entries()));
                    headers.set('Content-Type', 'application/json');
                    break;
                case 'form-data':
                    data = formData;
                    break;
                case 'x-www-form-urlencoded':
                    data = new URLSearchParams(formData).toString();
                    headers.set('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    break;
            }

            settings = {
                method: this.requestMethod,
                headers: headers,
            };

            if (this.requestMethod === 'GET'){
                let getUrl = new URL(this.endpoint);
                getUrl.search = new URLSearchParams(formData).toString();
                request = new Request(getUrl.toString());
            } else {
                request = new Request(this.endpoint);
                settings.body = data;
            }

            this.doingAjax = true;
            this.subscriptionSuccess = null;

            fetch(request, settings).then(response => {
                let contentType = response.headers.get("content-type");

                if (!response.ok) {
                    form.dispatchEvent(new CustomEvent('rempXhrError', {
                        detail: {
                            type: 'httpResponse',
                            message: 'Request to newsletter backend API not successful',
                            response: response
                        },
                        bubbles: true,
                        cancelable: true
                    }));
                    this.subscriptionSuccess = false;
                }

                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                }

                throw new TypeError("Response is not a JSON");

            }).then((data) => {
                if (this.isFailed(data) || this.subscriptionSuccess === false) {
                    this.failureMessage = this.getFailureMessage(data);
                    this.subscriptionSuccess = false;
                } else {
                    this.subscriptionSuccess = true;
                }
            }).catch((error) => {
                this.subscriptionSuccess = false;
                form.dispatchEvent(new CustomEvent('rempXhrError', {
                    detail: {
                        type: 'exception',
                        message: error
                    },
                    bubbles: true,
                    cancelable: true
                }));
            }).finally(() => {
                this.doingAjax = false;
            });
        },
        isFailed: function(data){
            return (
                this.responseFailure.hasOwnProperty('status_param') &&
                this.responseFailure.hasOwnProperty('status_param_value') &&
                data.hasOwnProperty(this.responseFailure['status_param']) &&
                data[this.responseFailure['status_param']] === this.responseFailure['status_param_value']
            );
        },
        getFailureMessage: function(data){
            if (
                this.responseFailure.hasOwnProperty('message_param') &&
                data.hasOwnProperty(this.responseFailure['message_param'])
            ) {
                return data[this.responseFailure['message_param']]
            }
            return '';
        },
        clearLastResponse: function(event){
            this.subscriptionSuccess = null;
        },
        // Searches str for ${var}, where var is property of obj and replaces it with property value
        renderString: function(str, obj){
            return str.replace(/\$\{(.+?)\}/g, (match, p1) => {
                return this._index(obj, p1)
            });
        },
        _index: function(obj, is, value) {
            if (typeof is == 'string') {
                is = is.split('.');
            }
            if (is.length === 1 && value !== undefined) {
                return obj[is[0]] = value;
            } else if (is.length === 0) {
                return obj;
            } else {
                return this._index(obj[is[0]], is.slice(1), value);
            }
        }
    },
    computed: {
        _btnSubmit: function(){
            if (this.doingAjax){
                return '';
            }
            if (this.subscriptionSuccess === true ){
                return this.success;
            }
            if (this.subscriptionSuccess === false){
                return this.failure;
            }
            return this.btnSubmit;
        },
        _source: function(){
            return 'newsletter-rectangle';
        },
        _referer: function (){
            if (window && window.location && window.location.href){
                return window.location.href;
            }
            if (location && location.href){
                return location.href;
            }
        },
        _terms: function (){
            if(!this.urlTerms || !this.terms){
                return '';
            }
            let css = `<style>.newsletter-rectangle-form a {color: ${this.buttonBackgroundColor}</style>`;
            return this.renderString(this.terms, {url: this.urlTerms}) + css;
        },
        _position: function () {
            if (!this.$parent.customPositioned()) {
                return {};
            }

            if (this.positionOptions[this.position]) {
                let styles = this.positionOptions[this.position].style;

                for (let ii in styles) {
                    styles[ii] = ((ii === 'top' || ii === 'bottom') ? this.offsetVertical : this.offsetHorizontal) + 'px'
                }

                return styles;
            }

            return {};
        },
        containerStyles: function () {
            let position, zIndex;
            if (this.displayType === 'overlay') {
                position = 'fixed';
                zIndex = 9999;
            } else {
                position = 'relative'
            }
            if (typeof this.forcedPosition !== 'undefined') {
                position = this.forcedPosition;
            }
            return {
                position: position,
                zIndex: zIndex,
            }
        },
        boxStyles: function () {
            return {
                backgroundColor: this.backgroundColor,
                color: this.textColor,
                minWidth: this.width || '100px',
                maxWidth: this.width || '',
                minHeight: this.height || '250px',
                maxHeight: this.height || '',
            }
        },
        boxClass: function (){
            let width = parseInt(this.width);
            if (!isNaN(width) && width < 420){
                return 'compact';
            }
        },
        buttonStyles: function () {
            if (this.subscriptionSuccess === true || this.subscriptionSuccess === false){
                return {
                    backgroundColor: this.buttonTextColor,
                    color: this.buttonBackgroundColor,
                }
            }
            return {
                color: this.buttonTextColor,
                backgroundColor: this.buttonBackgroundColor,
            }
        },
        linkStyles: function () {
            return {
                color: this.textColor,
            }
        },
        isVisible: function () {
            return this.show && this.visible;
        },
    },
}
</script>
