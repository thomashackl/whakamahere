<template>
    <form class="default">
        <fieldset>
            <legend>
                Veranstaltung
            </legend>
            <section>
                <div class="label">{{ details.course }}</div>
                {{ details.semester }}
            </section>
            <section class="col-3">
                <div class="label">Turnus:</div>
                <template v-if="details.cycle == 1">
                    wöchentlich
                </template>
                <template v-else>
                    alle {{ details.cycle }} Wochen
                </template>
            </section>
            <section class="col-3">
                <div class="label">Startwoche:</div>
                startet in der {{ details.startweek + 1 }}. Vorlesungswoche
                <div class="label">Ende:</div>
                <template v-if="details.end_offset == 0">
                    endet zum Vorlesungsende
                </template>
                <template v-else-if="details.end_offset == 1">
                    endet eine Woche vor Vorlesungsende
                </template>
                <template v-else>
                    endet {{ details.end_offset }} Wochen vor Vorlesungsende
                </template>
            </section>
            <section class="col-3">
                <div class="label">Lehrende(r):</div>
                {{ details.lecturer }}
            </section>
        </fieldset>
        <fieldset>
            <legend>Angaben zur Planung</legend>
            <section class="col-3">
                <div class="label">Wunschzeit:</div>
                {{ weekday }} {{ details.time }}
            </section>
            <section class="col-3">
                <div class="label">Dauer:</div>
                {{ details.duration }} Minuten
            </section>
            <section class="col-3">
                <div class="label">Wunschraum:</div>
                {{ details.room }}
            </section>
            <section class="col-3">
                <div class="label">Raumanforderungen:</div>
                <ul>
                    <li v-for="prop in details.property_requests" :key="prop.id">
                        {{ prop.name }}: {{ prop.value }}
                    </li>
                </ul>
            </section>
            <section v-if="details.comment != ''">
                <div class="label">Kommentar:</div>
                {{ details.comment }}
            </section>
        </fieldset>
        <fieldset v-if="details.bookings.length > 0">
            <legend>Bereits gebucht</legend>
            <ul>
                <li v-for="booking in details.bookings" :key="booking.booking_id">
                    {{ booking.begin }} - {{ booking.end }}: {{ booking.room }}
                </li>
            </ul>
        </fieldset>
    </form>
</template>

<script>
    import { globalfunctions } from '../mixins/globalfunctions'

    export default {
        name: 'SlotDetails',
        mixins: [
            globalfunctions
        ],
        props: {
            details: {
                type: Object,
                required: true
            }
        },
        computed: {
             weekday: function() {
                 const day = this.getWeekdays().filter((weekday) => weekday.number == this.details.weekday)
                 return day[0].name
             }
        }
    }
</script>

<style lang="scss">
    div.label {
        font-weight: bold;
    }
</style>
