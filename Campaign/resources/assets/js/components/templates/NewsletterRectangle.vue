<template>
    <div>
        <ul class="tab-nav" role="tablist" data-tab-color="teal">
            <li class="active">
                <a href="#newsletter-rectangle" role="tab" data-toggle="tab" aria-expanded="true">Newsletter Rectangle</a>
            </li>
        </ul>

        <div class="card m-t-15">
            <div class="tab-content p-0">
                <div role="tabpanel" class="active tab-pane" id="newsletter-rectangle">
                    <div class="card-body card-padding p-l-15">

                        <div class="input-group fg-float m-t-30">
                            <span class="input-group-addon"><i class="zmdi zmdi-email"></i></span>
                            <div class="fg-line">
                                <label for="newsletter_id" class="fg-label">Newsletter ID</label>
                                <input v-model="newsletterId" class="form-control fg-input remp-banner-text-input"
                                       name="newsletter_id" id="newsletter_id">
                            </div>
                        </div><!-- .input-group -->

                        <div class="input-group fg-float m-t-30">
                            <span class="input-group-addon"><i class="zmdi zmdi-n-1-square"></i></span>
                            <div class="fg-line">
                                <label for="btn_submit" class="fg-label">Submit Button Text</label>
                                <input v-model="btnSubmit" class="form-control fg-input remp-banner-text-input"
                                       name="btn_submit" id="btn_submit">
                            </div>
                        </div><!-- .input-group -->

                        <div class="input-group fg-float m-t-30">
                            <span class="input-group-addon"><i class="zmdi zmdi-format-subject"></i></span>
                            <div class="fg-line">
                                <label for="title" class="fg-label">Title</label>
                                <input v-model="title" class="form-control fg-input remp-banner-text-input"
                                       name="title" id="title">
                            </div>
                        </div><!-- .input-group -->

                        <div class="input-group fg-float m-t-30">
                            <span class="input-group-addon"><i class="zmdi zmdi-format-subject"></i></span>
                            <div class="fg-line">
                                <label for="text" class="fg-label">Text</label>
                                <textarea v-model="text" class="form-control fg-input remp-banner-text-input"
                                          rows="4" name="text" cols="50" id="text"></textarea>
                            </div>
                        </div><!-- .input-group -->

                        <div class="input-group fg-float m-t-30">
                            <span class="input-group-addon"><i class="zmdi zmdi-format-subject"></i></span>
                            <div class="fg-line">
                                <label for="success" class="fg-label">Success message</label>
                                <textarea v-model="success" class="form-control fg-input remp-banner-text-input"
                                          rows="2" name="success" cols="50" id="success"></textarea>
                            </div>
                        </div><!-- .input-group -->

                        <div class="input-group fg-float m-t-30">
                            <span class="input-group-addon"><i class="zmdi zmdi-format-subject"></i></span>
                            <div class="fg-line">
                                <label for="failure" class="fg-label">Failure message</label>
                                <textarea v-model="failure" class="form-control fg-input remp-banner-text-input"
                                          rows="2" name="failure" cols="50" id="failure"></textarea>
                            </div>
                        </div><!-- .input-group -->

                        <div class="input-group fg-float m-t-30">
                            <span class="input-group-addon"><i class="zmdi zmdi-link"></i></span>
                            <div class="fg-line">
                                <label for="url_terms" class="fg-label">Terms & Conditions Link</label>
                                <input v-model="urlTerms" class="form-control fg-input remp-banner-text-input"
                                       name="url_terms" id="url_terms">
                            </div>
                        </div><!-- .input-group -->

                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="template" value="newsletter_rectangle_template" />
    </div>
</template>

<script type="text/javascript">
let props = [
    '_templateId',
    '_newsletterId',
    '_btnSubmit',
    '_title',
    '_text',
    '_success',
    '_failure',
    '_urlTerms',
    '_endpoint',
    '_useXhr',
    '_requestMethod',
    '_requestBody',
    '_requestHeaders',
    '_paramsTr',
    '_paramsExtra',
];
export default {
    name: "newsletter-rectangle",
    components: {},
    props: props,
    created: function () {
        props.forEach((prop) => {
            this[prop.slice(1)] = this[prop] || this[prop.slice(1)];
        });

        if (!this.templateId) {
            this.newsletterId = '';
            this.btnSubmit = 'Subscribe';
            this.title = 'Headline';
            this.text = 'Lorem Ipsum...';
            this.success = 'Newsletter subscription successful. Thank you and welcome on board!';
            this.failure = 'Unfortunately subscription was not successful. Please find error message below.';
            this.urlTerms = 'https://example.com';
        }

        this.emitValuesChanged();
    },
    data: () => ({
        newsletterId: null,
        btnSubmit: null,
        title: null,
        text: null,
        success: null,
        failure: null,
        urlTerms: null,
        endpoint: null,
        useXhr: null,
        requestMethod: null,
        requestBody: null,
        requestHeaders: null,
        paramsTr: null,
        paramsExtra: null
    }),
    updated: function () {
        this.emitValuesChanged();
    },
    methods: {
        emitValuesChanged: function () {
            this.$parent.$emit("values-changed", [
                {
                    key: "newsletterRectangleTemplate",
                    val: {
                        newsletterId: this.newsletterId,
                        btnSubmit: this.btnSubmit,
                        title: this.title,
                        text: this.text,
                        success: this.success,
                        failure: this.failure,
                        urlTerms: this.urlTerms,
                        endpoint: this.endpoint,
                        useXhr: this.useXhr,
                        requestMethod: this.requestMethod,
                        requestBody: this.requestBody,
                        requestHeaders: this.requestHeaders,
                        paramsTr: this.paramsTr,
                        paramsExtra: this.paramsExtra
                    }
                },
            ]);
        }
    },
    computed: {}
}
</script>