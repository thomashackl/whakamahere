<template>
    <article>
        <header>
            <h1>{{ semester }}</h1>
        </header>
        <paginate v-if="total > entriesPerPage" v-model="currentPage"
                  prev-text="&lt; " next-text=" &gt;"
                  :page-count="numberOfPages" :click-handler="changePage"
                  container-class="whakamahere-paginate" page-class="whakamahere-page"></paginate>
        <vue-simple-spinner v-if="loading" size="64" message="Veranstaltungen werden geladen..."></vue-simple-spinner>
        <studip-messagebox v-if="!loading && theCourses.length == 0" :type="info"
                          message="Zu den aktuellen Filtereinstellungen wurden keine Veranstaltungen gefunden.">
        </studip-messagebox>
        <table v-if="!loading && theCourses.length > 0" class="default">
            <caption>
                Veranstaltung {{ start }} - {{ Math.min(end, total) }} von {{ total }}.
            </caption>
            <colgroup>
                <col>
                <col width="200">
                <col width="400">
            </colgroup>
            <thead>
                <tr>
                    <th>Veranstaltung</th>
                    <th>Lehrende</th>
                    <th>Gewünschte regelmäßige Zeit(en)</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="course in theCourses" :key="course.id">
                    <td>
                        <a :href="getCourseUrl(course.id, 'timesrooms')"
                           target="_blank">
                            {{ course.name }}
                        </a>
                    </td>
                    <td>
                        <div v-for="lecturer in course.lecturers" :key="lecturer.id">
                            {{ lecturer.name }}
                        </div>
                    </td>
                    <td>
                        <b v-if="typeof(course.request) != 'undefined'">
                            {{ course.request.slots.length }}
                            <template v-if="course.request.slots.length < 2">
                                regelmäßige Zeit:
                            </template>
                            <template v-else>
                                regelmäßige Zeiten:
                            </template>
                        </b>
                        <ul v-if="typeof(course.request) != 'undefined'">
                            <li v-for="slot in course.request.slots" :key="slot.id">{{ slot.name }}</li>
                        </ul>
                        <template v-else>unregelmäßig/Blockveranstaltung/kein Raum benötigt</template>
                    </td>
                </tr>
            </tbody>
        </table>
        <paginate v-if="!loading && total > entriesPerPage" v-model="currentPage"
                  prev-text="&lt; " next-text=" &gt;"
                  :page-count="numberOfPages" :click-handler="changePage"
                  container-class="whakamahere-paginate" page-class="whakamahere-page"></paginate>
    </article>
</template>

<script>
    import { globalfunctions } from '../mixins/globalfunctions'
    import Paginate from 'vuejs-paginate'
    import VueSimpleSpinner from 'vue-simple-spinner'
    import StudipMessagebox from '../studip/StudipMessagebox'

    export default {
        name: 'CourseListing',
        components: {
            Paginate,
            VueSimpleSpinner,
            StudipMessagebox
        },
        mixins: [
            globalfunctions
        ],
        props: {
            total: {
                type: Number,
                required: true
            },
            semester: {
                type: String,
                required: true
            },
            institute: {
                type: String,
                default: ''
            },
            courses: {
                type: Array,
                default: () => []
            }
        },
        data() {
            return {
                loading: false,
                theCourses: this.courses,
                start: 1,
                end: 100,
                entriesPerPage: 100,
                currentPage: 0
            }
        },
        computed: {
            numberOfPages: function() {
                return Math.ceil(this.total / this.entriesPerPage)
            }
        },
        methods: {
            changePage: function (pageNum) {
                this.loading = true
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/listing/courses/' +
                    ((pageNum - 1) * this.entriesPerPage) + '/' + this.entriesPerPage))
                .then((response) => {
                    if (!response.ok) {
                        throw response
                    }

                    response.json().then((json) => {
                        this.theCourses = json
                        this.start = ((pageNum - 1) * this.entriesPerPage) + 1
                        this.end = Math.min(
                            ((pageNum - 1) * this.entriesPerPage) + this.entriesPerPage,
                            this.total
                        )
                        this.loading = false
                    })
                }).catch((error) => {
                    this.loading = false
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            }
        }
    }
</script>

<style lang="scss">
    table {
        thead, tbody {
            tr {
                th, td {
                    vertical-align: top;

                    ul {
                        padding-left: 20px;
                    }
                }
            }
        }
    }
</style>
