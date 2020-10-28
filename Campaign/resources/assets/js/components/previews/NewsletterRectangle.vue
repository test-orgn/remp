<style type="text/css">
@import url('https://fonts.googleapis.com/css?family=Noto+Sans');
@import url('../../../sass/transitions.scss');

.newsletter-rectangle-preview-close {
    position: absolute;
    display: block;
    top: 20px;
    right: 20px;
    text-decoration: none;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

a.newsletter-rectangle-preview-close::after {
    content: "\00a0\00d7";
    font-size: 24px;
    vertical-align: sub;
    font-weight: normal;
}

.newsletter-rectangle-form {
    position: relative;
    max-width: 420px;
    margin: 0 auto;
    padding: 20px;
    font-size: 14px;
    line-height: 20px;
}

.newsletter-rectangle-title {
    font-size: 18px;
    line-height: 26px;
    font-weight: bold;
    margin-bottom: 15px;
}

.newsletter-rectangle-text {
    margin-bottom: 15px;
}

.newsletter-rectangle-form-label {
    display: none;
}

.newsletter-rectangle-form-inputs {
    margin: 20px 0;
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

@media only screen and (max-device-width: 600px) {
    .newsletter-rectangle-form {
    }
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
                           v-bind:name="_form('email')">

                    <input type="hidden" v-bind:name="_form('newsletterId')" v-bind:value="newsletterId">
                    <input type="hidden" v-bind:name="_form('source')" v-bind:value="_source">
                    <input type="hidden" v-bind:name="_form('referer')" v-bind:value="_referer">
                    <input v-for="(value, name) in paramsExtra" type="hidden" v-bind:name="name" v-bind:value="value">
                    <input class="newsletter-rectangle-form-button" type="submit"
                           v-bind:name="_form('submit')"
                           v-bind:value="btnSubmit"
                           v-bind:style="[buttonStyles]"
                           v-on:submit="_formSubmit">
                </fieldset>

                <div class="newsletter-rectangle-disclaimer" v-html="_terms" ></div>
            </form>

        </transition>

    </div>

</template>

<script>
import jQuery from 'jquery';

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
            let $form = jQuery(event.target);
            let data;
            let headers = {};
            let settings = {};

            if (!this.useXhr){
                this.$parent.clicked(event,true);
                return true;
            }

            event.preventDefault();
            this.$parent.clicked(event,false);

            switch (this.requestBody){
                case 'raw-body':
                    data = JSON.stringify($form.serializeArray());
                    headers = {
                        'Content-Type': 'application/json'
                    }
                    break;
                case 'form-data':
                    data = $form.serialize();
                    settings = {
                        "processData": false,
                        "mimeType": "multipart/form-data",
                        "contentType": false,
                    }
                    break;
                case 'x-www-form-urlencoded':
                    data = $form.serializeArray();
                    headers = {
                        "Content-Type": "application/x-www-form-urlencoded"
                    }
                    break;
            }

            jQuery.ajax({
                ...settings,
                type: this.requestMethod,
                url: this.endpoint,
                headers: {
                    ...headers,
                    ...this.requestHeaders,
                },
                data: data,
                success: (data) => {
                    console.log(data);
                },
                failure: (data) => {
                    console.log(data);
                }
            });

        },
        _renderString: function(str, obj){
            return str.replace(/\$\{(.+?)\}/g,(match,p1)=>{return this._index(obj,p1)})
        },
        _index: function(obj,is,value) {
            if (typeof is == 'string')
                is=is.split('.');
            if (is.length===1 && value!==undefined)
                return obj[is[0]] = value;
            else if (is.length===0)
                return obj;
            else
                return this._index(obj[is[0]],is.slice(1), value);
        }
    },
    computed: {
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
            return this._renderString(this.terms, {url: this.urlTerms}) + css;
        },
        _position: function () {
            if (!this.$parent.customPositioned()) {
                return {};
            }

            if (this.positionOptions[this.position]) {
                var styles = this.positionOptions[this.position].style;

                for (var ii in styles) {
                    styles[ii] = ((ii == 'top' || ii == 'bottom') ? this.offsetVertical : this.offsetHorizontal) + 'px'
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
                maxWidth: this.width || '370px',
                minHeight: this.height || '250px',
                maxHeight: this.height || '370px',
            }
        },
        buttonStyles: function () {
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
