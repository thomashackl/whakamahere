<template>
    <div>
        <paginate v-if="total > entriesPerPage" v-model="currentPage"
                  prev-text="&lt; " next-text=" &gt;"
                  :page-count="numberOfPages" :click-handler="changePage"
                  container-class="whakamahere-paginate" page-class="whakamahere-page"></paginate>
        <vue-simple-spinner v-if="loading" size="32" message="Logeinträge werden geladen..."></vue-simple-spinner>
        <table v-if="!loading" class="default">
            <caption>Veröffentlichungsprotokoll ({{ start }} - {{ Math.min(end, total) }}/{{ total }})</caption>
            <colgroup>
                <col width="20">
                <col>
                <col width="200">
                <col width="150">
                <col>
                <col width="150">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Veranstaltung</th>
                    <th>Regelmäßige Zeit</th>
                    <th>Gebuchter Raum</th>
                    <th>Status</th>
                    <th>Zeitstempel</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(entry, index) in theEntries" :key="entry.id" :class="getClass(entry.state)">
                    <td>{{ start + index }}.</td>
                    <td>
                        <a :href="getCourseUrl(entry.course.id, 'timesrooms')"
                           target="_blank">{{ entry.course.fullname }}</a>
                    </td>
                    <td>{{ getWeekdayName(entry.time.weekday) }}, {{ entry.time.start }} - {{ entry.time.end }}</td>
                    <td>{{ entry.booking.room }}</td>
                    <td>
                        <studip-icon v-if="entry.state === 'success'" shape="accept"></studip-icon>
                        <span v-else-if="entry.note != null" v-html="makeList(entry.note)"></span>
                    </td>
                    <td>{{ entry.mkdate }}</td>
                </tr>
            </tbody>
        </table>
        <paginate v-if="total > entriesPerPage && !loading" v-model="currentPage"
                  prev-text="&lt; " next-text=" &gt;"
                  :page-count="numberOfPages" :click-handler="changePage"
                  container-class="whakamahere-paginate" page-class="whakamahere-page"></paginate>
    </div>
</template>

<script>
    import { globalfunctions } from '../mixins/globalfunctions'
    import VueSimpleSpinner from 'vue-simple-spinner'
    import Paginate from 'vuejs-paginate'
    import StudipIcon from '../studip/StudipIcon'

    export default {
        name: 'PublishLogViewer',
        components: {
            VueSimpleSpinner,
            Paginate,
            StudipIcon
        },
        mixins: [
            globalfunctions
        ],
        props: {
            semester: {
                type: String,
                required: true
            },
            total: {
                type: Number,
                required: true
            },
            entries: {
                type: Array,
                default: () => []
            }
        },
        data() {
            return {
                loading: false,
                theEntries: this.entries,
                start: 1,
                end: 100,
                weekdays: [
                    'So',
                    'Mo',
                    'Di',
                    'Mi',
                    'Do',
                    'Fr',
                    'Sa'
                ],
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
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/log/get_entries/' +
                        ((pageNum - 1) * this.entriesPerPage) + '/' + this.entriesPerPage))
                .then((response) => {
                    if (!response.ok) {
                        throw response
                    }

                    response.json().then((json) => {
                        this.theEntries = json
                        this.start = ((pageNum - 1) * this.entriesPerPage) + 1
                        this.end = Math.min(
                            ((pageNum - 1) * this.entriesPerPage) + this.entriesPerPage,
                            this.total
                        )
                        this.loading = false
                    })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            },
            getClass: function (state) {
                switch (state) {
                    case 'success':
                        return 'publish-success';
                    case 'warning':
                        return 'publish-warning';
                    case 'error':
                        return 'publish-error';
                }
            },
            getWeekdayName: function (number) {
                return this.weekdays[number]
            },
            makeList: function(string) {
                const parts = string.split('\n')

                if (parts.length < 2) {

                    return string

                } else {

                    let list = '<ul>'

                    for (let i = 0 ; i < parts.length ; i++) {
                        list += '<li>' + parts[i] + '</li>'
                    }

                    list += '</ul>'

                    return list
                }
            }
        }
    }
</script>

<style lang="scss">
    table.default {
        thead, tbody {
            tr {
                &.publish-error {
                    background-color: #ef9999;
                }

                &.publish-warning {
                    background-color: #ffd785;
                }

                th, td {
                    vertical-align: top;

                    &:first-child, &:last-child {
                        text-align: right;
                    }

                    ul {
                        li {
                            margin-left: -25px;
                        }
                    }
                }
            }
        }
    }
</style>
