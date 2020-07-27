<template>
    <section id="whakamahere-seats-filter">
        <label for="min-seats">
            Erwartete Teilnehmer
        </label>
        <input type="number" name="min-seats" id="min-seats" :value="minimum" min="0"
               @change="changeMinSeats($event)" @keypress="checkEnter($event, 'min')">
        bis
        <input type="number" name="max-seats" id="max-seats" :value="maximum" min="0"
               @change="changeMaxSeats($event)" @keypress="checkEnter($event, 'max')">
    </section>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'SeatsFilter',
        props: {
            minSeats: {
                type: Number,
                default: 0
            },
            maxSeats: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                minimum: this.minSeats,
                maximum: this.maxSeats
            }
        },
        methods: {
            changeMinSeats(event) {
                event.preventDefault()
                this.minimum = isNaN(event.target.valueAsNumber) ? 0 : event.target.valueAsNumber
                if (this.maximum != 0 && this.maximum < this.minimum) {
                    this.maximum = this.minimum
                }
                bus.$emit('updated-seats', JSON.stringify({min: this.minimum, max: this.maximum}))
            },
            changeMaxSeats(event) {
                event.preventDefault()
                this.maximum = isNaN(event.target.valueAsNumber) ? 0 : event.target.valueAsNumber
                if (this.maximum != 0 && this.maximum < this.minimum) {
                    this.minimum = this.maximum
                }
                bus.$emit('updated-seats', JSON.stringify({min: this.minimum, max: this.maximum}))
            },
            checkEnter: function(event, minMax) {
                if (event.which == 13) {
                    event.preventDefault()
                    if (minMax == 'min') {
                        this.changeMinSeats(event)
                    } else {
                        this.changeMaxSeats(event)
                    }
                }
            }
        }
    }
</script>
