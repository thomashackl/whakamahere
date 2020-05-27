<template>
    <table class="default" v-if="!loading && rooms.length > 0">
        <caption>
            <strong>{{ course }}</strong>
            <br>
            ({{ seats }} Sitzplätze)
        </caption>
        <colgroup>
            <col>
            <col>
            <col>
            <col>
            <col>
        </colgroup>
        <thead>
            <tr>
                <th>Raum</th>
                <th>Sitzplätze</th>
                <th>Eignung</th>
                <th>Verfügbarkeit</th>
                <th>Ausstattung</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="room in rooms" :key="room.id" :class="getRoomClass(room)" @click="selectRoom(room.id)">
                <td>{{ room.name }}</td>
                <td>{{ room.seats }}</td>
                <td>{{ Math.round(room.score) }}%</td>
                <td>
                    <template v-if="room.occupied.length == 0">
                        immer frei
                    </template>
                    <template v-else>
                        belegt am:
                        <ul>
                            <li v-for="booking in room.occupied" :key="booking.id">
                                {{ getDate(booking.begin) }} - {{ getDate(booking.end, true) }}
                            </li>
                        </ul>
                    </template>
                </td>
                <td>
                    <template v-if="room.missing_properties.length == 0">
                        vollständig
                    </template>
                    <template v-else>
                        fehlt:
                        <ul>
                            <li v-for="(property, index) in room.missing_properties" :key="index">
                                {{ property }}
                            </li>
                        </ul>
                    </template>
                </td>
            </tr>
        </tbody>
    </table>
    <studip-messagebox v-else-if="loading" type="info" message="Räume werden geladen..."></studip-messagebox>
    <studip-messagebox v-else-if="!loading && rooms.length == 0" type="warning"
                       message="Keine passenden freien Räume gefunden"></studip-messagebox>
</template>

<script>
    import StudipMessagebox from './StudipMessagebox'
    import { globalfunctions } from './mixins/globalfunctions'

    export default {
        name: 'RoomProposals',
        components: {
            StudipMessagebox
        },
        mixins: [
            globalfunctions
        ],
        props: {
            timeId: {
                type: Number,
                required: true
            }
        },
        data() {
            return {
                course: '',
                loading: true,
                seats: 0,
                rooms: []
            }
        },
        mounted() {
            fetch(
                STUDIP.URLHelper.getURL(this.$pluginBase + '/planning/room_proposals/' + this.timeId)
            ).then((response) => {
                if (!response.ok) {
                    throw response
                }
                response.json().then((json) => {
                    this.timeId = json.time_id
                    this.course = json.course
                    this.seats = json.seats
                    this.rooms = json.rooms
                    this.loading = false
                })
            }).catch((error) => {
                this.showErrorMessage(error)
            })
        },
        methods: {
            getRoomClass: function(room) {
                if (room.score > 100) {
                    return 'room-preference'
                } else if (room.score >= 85) {
                    return 'room-good'
                } else if (room.score >= 70) {
                    return 'room-okay'
                } else {
                    return 'room-warning'
                }
            },
            selectRoom: function(roomId) {
                alert(roomId)
            },
            getDate: function(timestamp, short) {
                if (short == null) {
                    short = false
                }

                const date = new Date()
                date.setTime(timestamp * 1000)

                let options = {
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: false
                }
                if (short) {
                    options = {
                        hour: 'numeric',
                        minute: 'numeric',
                        hour12: false
                    }
                }
                return new Intl.DateTimeFormat('de-DE', options).format(date)
            }
        }
    }
</script>

<style lang="scss">
    div[role="dialog"] {
        max-width: 90%;
    }

    table.default {
        caption {
            font-size: small;
        }

        tbody {
            tr {
                cursor: pointer;

                &.room-preference {
                    background-color: #008512;
                    color: #ffffff;
                }
                &.room-good {
                    background-color: #6ead10;
                }
                &.room-okay {
                    background-color: #a8ce70;
                }
                &.room-warning {
                    background-color: #ffd785;
                }
            }
        }
    }
</style>
