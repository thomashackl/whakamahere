<template>
    <div>
        <a href="" @click="publishNow">
            <studip-button label="Jetzt veröffentlichen" class="whakamahere-publish"></studip-button>
        </a>
        <div>
            <template v-if="!loading && courseIds.length > 0">
                {{ courseIds.length }} Veranstaltungen
            </template>
            <template v-if="loading">
                Daten werden geladen...
            </template>
            <template v-if="processing && courseIds.length > 0">
                <progress :value="processed" :max="courseIds.length"></progress>
                <br>
                {{ processed }} / {{ courseIds.length }}
            </template>
        </div>
    </div>
</template>

<script>
    import StudipButton from '../studip/StudipButton'

    export default {
        name: 'PublishPlanning',
        components: {
            StudipButton
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
                processed: 0
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
                    STUDIP.URLHelper.getURL(this.$pluginBase + '/dashboard/courses')
                ).then((response) => {
                    if (!response.ok) {
                        throw response
                    }
                    response.json()
                        .then((json) => {
                            this.courseIds = json
                            this.loading = false
                            this.processing = true
                            this.processed = 0
                            json.forEach((course) => {
                                fetch(
                                    STUDIP.URLHelper.getURL(this.$pluginBase + '/dashboard/publish/' + course)
                                ).then((response) => {
                                    if (!response.ok) {
                                        throw response
                                    }
                                    this.processed++
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
    button.button.whakamahere-publish {
        background-color: #d60000;
        color: #ffffff;
        font-size: x-large;
    }
</style>
