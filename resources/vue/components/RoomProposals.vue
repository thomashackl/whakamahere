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
            <col width="24">
        </colgroup>
        <thead>
            <tr>
                <th>Raum</th>
                <th>Sitzplätze</th>
                <th>Eignung</th>
                <th>Verfügbarkeit</th>
                <th>Ausstattung</th>
                <th>Buchen</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="room in rooms" :key="room.id" :class="getRoomClass(room)">
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
                <td>
                    <a href="" @click="selectRoom($event, room.id, room.name)">
                        <studip-icon shape="room-clear" height="24" width="24"></studip-icon>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
    <studip-messagebox v-else-if="loading" type="info" message="Räume werden geladen..."></studip-messagebox>
    <studip-messagebox v-else-if="!loading && rooms.length == 0" type="warning"
                       message="Keine passenden freien Räume gefunden"></studip-messagebox>
</template>

<script>
    import bus from 'jsassets/bus'
    import StudipIcon from './StudipIcon'
    import StudipMessagebox from './StudipMessagebox'
    import { globalfunctions } from './mixins/globalfunctions'

    export default {
        name: 'RoomProposals',
        components: {
            StudipIcon,
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
                slot: '',
                loading: true,
                seats: 0,
                rooms: []
            }
        },
        mounted() {
            fetch(
                STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/room_proposals/' + this.timeId)
            ).then((response) => {
                if (!response.ok) {
                    throw response
                }
                response.json().then((json) => {
                    this.timeId = json.time_id
                    this.course = json.course
                    this.slot = json.slot_id
                    this.seats = json.seats
                    this.rooms = json.rooms
                    this.loading = false
                })
            }).catch((error) => {
                this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
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
            selectRoom: function(event, roomId, roomName) {
                event.preventDefault()

                let formData = new FormData()
                formData.append('room', roomId)
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/book_room/' + this.timeId), {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    // An error occurred.
                    if (!response.ok) {
                        throw response
                    }

                    if (response.status == 206) {
                        this.showMessage('warning', 'Teilweise erfolgreich',
                            'Die Raumbuchungen konnten nur teilweise gespeichert werden.')

                        response.json().then((json) => {
                            bus.$emit('room-booked', { slot: this.slot, bookings: json, partial: true })
                        })
                    } else {
                        this.showMessage('success', 'Erfolgreich',
                            'Die Raumbuchungen wurden gespeichert.')

                        response.json().then((json) => {
                            bus.$emit('room-booked', { slot: this.slot, bookings: json })
                        })
                    }
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
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
