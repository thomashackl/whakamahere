<template>
    <div>
        <table v-if="statistics.length > 0" class="default">
            <caption v-if="unplanned > 0">
                {{ unplanned }} regelmäßige Veranstaltungszeiten sind noch nicht geplant!
            </caption>
            <thead>
                <tr>
                    <th>Einrichtung</th>
                    <th>Planungsrelevante Veranstaltungen</th>
                    <th>Regelmäßige Zeiten</th>
                    <th>Zeit geplant</th>
                    <th>Zeit und Raum geplant</th>
                    <th>Erfüllte Zeitwünsche</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in statistics">
                    <td>{{ row.institute }}</td>
                    <td class="number">{{ row.courses }}</td>
                    <td class="number">{{ row.slots }}</td>
                    <td class="number">{{ row.timePlanned }}</td>
                    <td class="number">{{ row.timeAndRoomPlanned }}</td>
                    <td class="number">
                        {{ row.timePlanned != 0 ? getPercentage(row.fulfilled, row.timePlanned) + '%' : '-' }}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Gesamt</td>
                    <td class="number">{{ sum.courses }}</td>
                    <td class="number">{{ sum.slots }}</td>
                    <td class="number">{{ sum.timePlanned }}</td>
                    <td class="number">{{ sum.timeAndRoomPlanned }}</td>
                    <td class="number">{{ sum.percentage }} %</td>
                </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
    import { globalfunctions } from '../mixins/globalfunctions'

    export default {
        name: 'ShortStatistics',
        mixins: [
            globalfunctions
        ],
        props: {
            semester: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                statistics: [],
                unplanned: 0
            }
        },
        computed: {
            sum: function() {
                let summed = {
                    courses: 0,
                    slots: 0,
                    timePlanned: 0,
                    timeAndRoomPlanned: 0,
                    fulfilled: 0
                }
                let percentages = []

                for (let i = 0 ; i < this.statistics.length ; i++) {
                    summed.courses += this.statistics[i].courses
                    summed.slots += this.statistics[i].slots
                    summed.timePlanned += this.statistics[i].timePlanned
                    summed.timeAndRoomPlanned += this.statistics[i].timeAndRoomPlanned
                    if (this.statistics[i].timePlanned != 0) {
                        percentages.push(this.statistics[i].fulfilled / this.statistics[i].timePlanned)
                    }
                }

                summed.percentage = this.getPercentage(percentages
                    .reduce((pv, cv) => pv + cv, 0), percentages.length)

                return summed
            }
        },
        mounted() {
            this.loadStatistics()
        },
        methods: {
            getPercentage(num, den) {
                if (den != 0) {
                    return Math.round(((num / den) * 10000)) / 100
                } else {
                    return 0
                }
            },
            loadStatistics: function() {
                fetch(
                    STUDIP.URLHelper.getURL(this.$pluginBase + '/dashboard/statistics')
                ).then((response) => {
                    if (!response.ok) {
                        throw response
                    }
                    response.json()
                        .then((json) => {
                            this.statistics = json.institutes
                            this.unplanned = json.unplanned.length
                        })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            }
        }
    }
</script>

<style lang="scss">
    table.default {
        tr {
            td {
                text-align: right;
            }
        }

        tfoot {
            font-weight: bold;

            tr {
                td {
                    padding: 5px;
                }
            }
        }
    }
</style>
