<template>
    <div>
        <a v-if="!loading && !processing" href="" @click="publishNow">
            <studip-button label="Jetzt veröffentlichen" class="whakamahere-publish"></studip-button>
        </a>
        <div id="publish-details">
            <template v-if="loading">
                <vue-simple-spinner size="32" message="Veranstaltungen werden geladen..."></vue-simple-spinner>
            </template>
            <div v-if="!loading && courseIds.length > 0">
                {{ courseIds.length }} Veranstaltungen werden veröffentlicht.
            </div>
            <div v-if="processing && courseIds.length > 0">
                <progress :value="processed" :max="courseIds.length"></progress>
                <br>
                {{ processed }} / {{ courseIds.length }} abgeschlossen
                <div v-if="successful > 0" class="publish-success">
                    {{ successful }} Veranstaltungen erfolgreich gebucht.
                </div>
                <div v-if="warning > 0" class="publish-warning">
                    {{ warning }} Veranstaltungen teilweise erfolgreich gebucht.
                </div>
                <div v-if="error > 0" class="publish-error">
                    {{ error }} Veranstaltungen nicht gebucht.
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import StudipButton from '../studip/StudipButton'
    import VueSimpleSpinner from 'vue-simple-spinner'

    export default {
        name: 'PublishPlanning',
        components: {
            StudipButton,
            VueSimpleSpinner
        },
        props: {
            semester: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                courseIds: [],
                loading: false,
                processing: false,
                processed: 0,
                successful: 0,
                warning: 0,
                error: 0,
                errors : []
            }
        },
        methods: {
            publishNow: function(event) {
                event.preventDefault()
                this.getCourses()
            },
            getCourses: function() {
                this.loading = true
                fetch(
                    STUDIP.URLHelper.getURL(this.$pluginBase + '/publish/get_courses')
                ).then((response) => {
                    this.loading = false
                    if (!response.ok) {
                        throw response
                    }
                    response.json()
                        .then((json) => {
                            this.courseIds = json
                            this.processing = true
                            this.processed = 0
                            json.forEach((course) => {
                                fetch(
                                    STUDIP.URLHelper.getURL(this.$pluginBase + '/publish/course/' + course)
                                ).then((response) => {
                                    if (!response.ok) {
                                        throw response
                                    }
                                    this.processed++

                                    response.json().then((json) => {
                                        switch (json.status) {
                                            case 'success':
                                                this.successful++
                                                break;
                                            case 'warning':
                                                this.warning++
                                                break;
                                            case 'error':
                                                this.error++
                                                break;
                                        }
                                    })
                                })
                            })
                        })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                    this.loading = false
                })
            }
        }
    }
</script>

<style lang="scss">
    a {
        button.button.whakamahere-publish {
            background-color: #d60000;
            color: #ffffff;
            font-size: x-large;
        }
    }

    #publish-details {
        div {
            padding: 10px;

            progress {
                border: 1px solid #28497c;
                border-radius: 0;
                height: 25px;
                max-width: 800px;
                min-width: 400px;
                width: 80%;
            }

            progress::-webkit-progress-bar {
                background-color: #ffffff;
            }
            progress::-webkit-progress-value {
                background-color: #28497c;
            }

            div.publish-success {
                color: #038511;
            }

            div.publish-warning {
                color: #ffbd33;
                font-style: italic;
                font-weight: bold;
            }

            div.publish-error {
                color: #d60000;
                font-weight: bold;
            }

            div.errors {
                color: #d60000;
                font-weight: bold;
                text-align: left;
            }
        }
    }
</style>
