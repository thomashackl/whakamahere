<template>
    <div>
        <fieldset>
            <legend>
                Meine Veranstaltung ist
            </legend>
            <section class="col-3">
                <input type="radio" name="regular" id="is-regular" v-model="regular" v-bind:value="1">
                <label class="undecorated" for="is-regular">
                    regelmäßig
                </label>
            </section>
            <section class="col-3">
                <input type="radio" name="regular" id="is-irregular" v-model="regular" v-bind:value="0">
                <label class="undecorated" for="is-irregular">
                    unregelmäßig oder eine Blockveranstaltung
                </label>
            </section>
        </fieldset>
        <fieldset v-if="regular == 1">
            <legend>Raumanforderungen</legend>
            <section>
                <label for="seats">
                    <span class="required">
                        Benötigte Sitzplätze
                    </span>
                </label>
                <input type="number" :name="'property_requests[' + seatsId + ']'" id="seats" min="1"
                       :value="request.property_requests[seatsId]">
            </section>
            <template v-for="oneProp in properties">
                <section v-if="oneProp.id != seatsId" :key="oneProp.id">
                    <input v-if="oneProp.type == 'bool'" type="checkbox" :name="'property_requests[' + oneProp.id + ']'"
                           :id="oneProp.id" value="1" :checked="request.property_requests[oneProp.id] == 1">
                    <label class="undecorated" :for="oneProp.id">
                        {{ oneProp.display_name }}
                    </label>
                    <input v-if="oneProp.type == 'num'" type="number" :name="'property_requests[' + oneProp.id + ']'"
                           :id="oneProp.id" :value="request.property_requests[oneProp.id]">
                    <input v-if="oneProp.type == 'url'" type="url" :name="'property_requests[' + oneProp.id + ']'"
                           :id="oneProp.id" :value="request.property_requests[oneProp.id]">
                    <input v-if="oneProp.type == 'text'" type="text" :name="'property_requests[' + oneProp.id + ']'"
                           :id="oneProp.id" :value="request.property_requests[oneProp.id]">
                </section>
            </template>
            <section>
                <label for="room">
                    Raumwunsch
                </label>
                <select2 :options="theRooms" :value="request.room_id" id="room" name="room_id"></select2>
            </section>
        </fieldset>
        <fieldset v-if="regular == 1" ref="slots" id="slots">
            <legend>
                <span class="required">Veranstaltungsterminwünsche</span>
            </legend>
            <course-slot v-for="(slot, index) in request.slots" :key="index" :number="index"
                         :lecturers="lecturers" :data="slot"></course-slot>
            <studip-button icon="add" name="add-slot" id="add-slot" label="Regelmäßigen Termin hinzufügen"
                           event-name="add-slot"></studip-button>
        </fieldset>
        <fieldset v-if="regular == 1">
            <legend>Sonstige Daten</legend>
            <section>
                <label for="cycle">
                    Turnus
                </label>
                <select name="cycle" id="cycle">
                    <option value="1" :selected="request.cycle == 1">wöchentlich</option>
                    <option value="2" :selected="request.cycle == 2">zweiwöchentlich</option>
                    <option value="3" :selected="request.cycle == 3">dreiwöchentlich</option>
                    <option value="4" :selected="request.cycle == 4">vierwöchentlich</option>
                </select>
            </section>
            <section>
                <label for="startweek">
                    In welcher Woche der Vorlesungszeit beginnt die Veranstaltung?
                </label>
                <select name="startweek" id="startweek">
                    <option v-for="(week, index) in startWeeks" :key="index" :value="index"
                            :selected="index == request.startweek">{{ week }}</option>
                </select>
            </section>
            <section>
                <label for="end-offset">
                    In welcher Woche der Vorlesungszeit endet die Veranstaltung?
                </label>
                <select name="end_offset" id="end-offset">
                    <option v-for="(week, index) in endWeeks" :key="index" :value="index"
                            :selected="index == request.end_offset">{{ week }}</option>
                </select>
            </section>
            <section>
                <label for="comment">
                    Notiz an die Raumvergabe
                </label>
                <textarea name="comment" id="comment" cols="75" rows="3">{{ request.comment }}</textarea>
            </section>
        </fieldset>
        <studip-messagebox v-if="regular == 0" :type="info"
                           message="Für unregelmäßige Veranstaltungen sind hier keine weiteren Angaben erforderlich. Bitte wenden Sie sich wegen Ihren benötigten Räumen direkt an die Raumvergabe.">
        </studip-messagebox>
    </div>
</template>

<script>
    import bus from 'jsassets/bus'
    import CourseSlot from '../planning/CourseSlot'
    import Select2 from '../common/Select2'
    import StudipButton from '../studip/StudipButton'
    import StudipMessagebox from '../studip/StudipMessagebox'
    var SlotClass = Vue.extend(CourseSlot)

    export default {
        name: 'PlanningRequest',
        components: {
            Select2,
            CourseSlot,
            StudipButton,
            StudipMessagebox
        },
        props: {
            regular: {
                type: Number,
                default: 1
            },
            seatsId: {
                type: String
            },
            lecturers: {
                type: Array
            },
            properties: {
                type: Array,
                default: () => []
            },
            rooms: {
                type: Array
            },
            startWeeks: {
                type: Array
            },
            endWeeks: {
                type: Array
            },
            request: {
                type: Object
            }
        },
        computed: {
            theRooms: function() {
                let options = [{
                    id: '',
                    text: '-- kein Raumwunsch --',
                    children: []
                }]

                for (let i = 0 ; i < this.rooms.length ; i++) {
                    options.push({
                        id: this.rooms[i].id,
                        text: this.rooms[i].name,
                        children: []
                    })
                }

                return options
            }
        },
        mounted() {
            bus.$on('add-slot', () => {
                this.addSlot()
            })
        },
        methods: {
            addSlot: function() {
                var newSlot = new SlotClass({
                    propsData: {
                        number: this.$el.querySelectorAll('.course-slot').length + 1,
                        lecturers: this.lecturers
                    }
                })
                newSlot.$mount()
                this.$refs.slots.insertBefore(newSlot.$el, document.getElementById('add-slot'))
            }
        }
    }
</script>

<style lang="scss">
    #slots {
        button {
            clear: both;
            display: block;
            margin-top: 10px;
        }
    }

</style>
