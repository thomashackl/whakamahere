<template>
    <article class="course-slot">
        <header>
            Regelmäßiger Termin {{ internalNumber }}
            <studip-icon shape="trash" size="20" role="info_alt" @click="removeMe"></studip-icon>
            <input v-if="data.slot_id != null" type="hidden" :name="'slots[' + internalNumber + '][slot_id]'"
                   :value="data.slot_id">
        </header>
        <section>
            <label :for="'lecturer-' + internalNumber">
                <span class="required">Dozent/in</span>
            </label>
            <select :id="'lecturer-' + internalNumber" :name="'slots[' + internalNumber + '][user_id]'">
                <option value="">N. N.</option>
                <option v-for="person in lecturers" :key="person.id" :value="person.id"
                        :selected="person.id == data.user_id">{{ person.name }}</option>
            </select>
        </section>
        <section>
            <label :for="'duration-' + internalNumber">
                <span class="required">Dauer</span>
            </label>
            <input type="number" :id="'duration-' + internalNumber"
                   :name="'slots[' + internalNumber + '][duration]'" :value="data.duration"
                   min="30" max="480" step="30">
        </section>
        <section>
            <span class="required">Zeitpräferenz</span>
            <label :for="'weekday-' + internalNumber">
                Wochentag
            </label>
            <select :id="'weekday-' + internalNumber" :name="'slots[' + internalNumber + '][weekday]'">
                <option v-for="day in weekdays" :key="day.number" :value="day.number"
                        :selected="day.number == data.weekday">{{ day.name }}</option>
            </select>
        </section>
        <section>
            <label :for="'time-' + internalNumber">
                Uhrzeit
            </label>
            <input type="time" :id="'time-' + internalNumber" :name="'slots[' + internalNumber + '][time]'"
                   :value="data.time" min="08:00" max="22:00">
        </section>
    </article>
</template>

<script>
    import bus from 'jsassets/bus'
    import { globalfunctions } from './mixins/globalfunctions'
    import StudipIcon from './StudipIcon'

    export default {
        name: 'CourseSlot',
        components: {
            StudipIcon
        },
        mixins: [
            globalfunctions
        ],
        props: {
            number: {
                type: Number
            },
            id: {
                type: Number,
                default: 0
            },
            lecturers: {
                type: Array
            },
            data: {
                type: Object,
                default: () => {
                    return {
                        lecturer: '',
                        duration: 60,
                        weekday: 1,
                        time: '08:00'
                    }
                }
            }
        },
        data() {
            return {
                internalNumber: this.number,
                weekdays: this.getWeekdays()
            }
        },
        mounted() {
            bus.$on('remove-slot', (number) => {
                if (this.internalNumber > number) {
                    this.internalNumber--
                }
            })
        },
        methods: {
            removeMe: function(event) {
                this.$el.remove()
                bus.$emit('remove-slot', this.number)
            }
        }
    }
</script>

<style lang="scss">
    .course-slot {
        border: 1px solid #bbbbbb;
        float: left;
        margin: 2px;
        margin-bottom: 10px;
        padding: 5px;
        width: 300px;

        header {
            background-color: #bbbbbb;
            color: #ffffff;
            margin: 2px;
            padding: 5px;

            img, svg {
                cursor: pointer;
                float: right;
            }
        }

        section {
            margin: 5px;
        }
    }
</style>
