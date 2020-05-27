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
        </fieldset>
    </form>
</template>

<script>
    import { globalfunctions } from './mixins/globalfunctions'

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
