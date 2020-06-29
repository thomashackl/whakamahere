<template>
    <table v-if="statistics.length > 0" class="default">
        <caption>Statistik für das {{ semester.name }}</caption>
        <thead>
            <tr>
                <th>Einrichtung</th>
                <th>Planungsrelevante Veranstaltungen</th>
                <th>Regelmäßige Zeiten</th>
                <th>Zeit geplant</th>
                <th>Zeit und Raum geplant</th>
                <th>Erfüllte Wünsche</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in statistics">
                <td>{{ row.institute }}</td>
                <td>{{ row.courses }}</td>
                <td>{{ row.slots }}</td>
                <td>{{ row.timePlanned }}</td>
                <td>{{ row.timeAndRoomPlanned }}</td>
                <td>{{ getPercentage(row.fulfilled, row.timePlanned) }} %</td>
            </tr>
        </tbody>
    </table>
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
                statistics: []
            }
        },
        mounted() {
            this.loadStatistics()
        },
        methods: {
            getPercentage(num, den) {
                return Math.round(((num / den) * 10000)) / 100
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
                            this.statistics = json
                        })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            }
        }
    }
</script>
