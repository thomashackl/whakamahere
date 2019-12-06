<template>
    <ring-spinner v-if="loading"></ring-spinner>
    <k-gauge v-else title="Raumauslastung" :value="value" :min="min" :max="max"
             :format-function="formatPct"></k-gauge>
</template>

<script>
    import { RingSpinner } from 'vue-spinners-css'
    import KGauge from '@kagronick/kgauge-vue'

    export default {
        name: 'StatisticsGauge',
        components: {
            RingSpinner,
            KGauge
        },
        props: {
            min: {
                type: Number,
                default: 0
            },
            max: {
                type: Number,
                default: 100
            },
            getValueUrl: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                value: 0,
                loading: true
            }
        },
        mounted() {
            console.log('Loading room occupation statistics...')
            this.getValue()
        },
        methods: {
            async getValue() {
                fetch(this.getValueUrl)
                    .then((response) => {
                        console.log('Got response')
                        response.json().then((json) => {
                            console.log(json)
                            this.loading = false
                            this.value = json.totalUsage * 100
                        })
                    })
            }
        }
    }
</script>
