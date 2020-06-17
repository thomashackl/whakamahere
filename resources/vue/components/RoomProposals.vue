<template>
    <article>
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
                        <template v-else-if="!room.always_occupied">
                            teilweise belegt:
                            <ul>
                                <li v-for="booking in room.occupied" :key="booking.id">
                                    {{ getDate(booking.begin) }} - {{ getDate(booking.end, true) }}
                                </li>
                            </ul>
                        </template>
                        <template v-else>
                            immer belegt
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
        <section v-if="!loading" id="manual-room-selection">
            <label class="default" for="search-room">
                Einen anderen Raum suchen
            </label>
            <br>
            <input type="text" name="manual_room" id="search-room" size="75" v-model="search"
                   placeholder="Geben Sie hier einen Teil eines Raumnamens ein">
            <studip-icon shape="search" width="20" height="20" @click="findRoomsManually"></studip-icon>
        </section>
    </article>
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
                search: '',
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
                let classes = ''
                if (room.manual) {
                    classes = 'manual '
                }

                if (room.always_occupied) {
                    classes += 'always-occupied '
                }

                if (room.score > 100) {
                    classes += 'room-preference'
                } else if (room.score >= 85) {
                    classes += 'room-good'
                } else if (room.score >= 70) {
                    classes += 'room-okay'
                } else if (room.score >= 50) {
                    classes += 'room-warning'
                } else {
                    classes += 'room-not-recommended'
                }

                return classes
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
                            bus.$emit('room-booked', { slot: this.slot, roomData: json, partial: true })
                        })
                    } else {
                        this.showMessage('success', 'Erfolgreich',
                            'Die Raumbuchungen wurden gespeichert.')

                        response.json().then((json) => {
                            bus.$emit('room-booked', { slot: this.slot, roomData: json })
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
            },
            findRoomsManually: function(event) {
                let formData = new FormData()
                formData.append('search', this.search)

                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/manual_room_search/' + this.timeId), {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    // An error occurred.
                    if (!response.ok) {
                        throw response
                    }

                    response.json().then((json) => {
                        if (json.length > 0) {

                            for (let i = 0 ; i < json.length ; i++) {
                                if (!this.rooms.find((element) => element.id == json[i].id)) {
                                    json[i].manual = true
                                    this.rooms.push(json[i])
                                }
                            }
                            this.rooms.sort((a, b) => { return b.score - a.score })
                        }
                        this.search = ''
                    })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            }
        }
    }
</script>

<style lang="scss">
    div[role="dialog"] {
        max-width: 90%;

        table.default {
            caption {
                font-size: small;
            }

            tbody {
                tr {
                    &.manual {
                        font-style: italic;
                    }
                    &.always-occupied {
                        text-decoration: line-through;
                    }
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
                    &.room-not-recommended {
                        background-color: #e76666;
                    }
                }
            }
        }
    }
</style>
