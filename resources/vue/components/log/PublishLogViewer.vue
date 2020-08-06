<template>
    <div>
        <paginate v-if="total > entriesPerPage" v-model="currentPage"
                  prev-text="&lt; " next-text=" &gt;"
                  :page-count="numberOfPages" :click-handler="changePage"
                  container-class="whakamahere-paginate" page-class="whakamahere-page"></paginate>
        <table class="default">
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
                        <a :href="getCourseUrl(entry.course.id)" target="_blank">{{ entry.course.fullname }}</a>
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
        <paginate v-if="total > entriesPerPage" v-model="currentPage"
                  prev-text="&lt; " next-text=" &gt;"
                  :page-count="numberOfPages" :click-handler="changePage"
                  container-class="whakamahere-paginate" page-class="whakamahere-page"></paginate>
    </div>
</template>

<script>
    import { globalfunctions } from '../mixins/globalfunctions'
    import Paginate from 'vuejs-paginate'
    import StudipIcon from '../studip/StudipIcon'

    export default {
        name: 'PublishLogViewer',
        components: {
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
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/log/get_entries/' +
                    this.semester + '/' +
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
                    })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            },
            getCourseUrl: function (id) {
                return STUDIP.URLHelper.getURL('dispatch.php/course/timesrooms', {cid: id})
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
    ul.whakamahere-paginate {
        background-color: #e7ebf1;
        border: 1px solid #d0d7e3;
        display: flex;
        flex-direction: row;
        height: 25px;
        margin-bottom: 10px;
        margin-top: 10px;
        padding-left: 10px;
        padding-right: 10px;

        li {
            line-height: 25px;
            list-style-type: none;
            padding-right: 5px;

            &:first-of-type.disabled, &:last-of-type.disabled {
                display: none;
            }

            &.whakamahere-page {
                text-align: center;
                width: 25px;

                &.active {
                    background-color: #24437c;
                    font-weight: bold;

                    a {
                        color: #ffffff;
                    }
                }
            }
        }
    }

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
