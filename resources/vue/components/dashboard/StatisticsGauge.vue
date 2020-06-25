<template>
    <div>
        <studip-loading-indicator v-if="loading" :is-loading="loading" :width="32" :height="32"/>
        <k-gauge title="Raumauslastung" :value="value" :min="min" :max="max"
                 :format-function="formatPct" :color-steps="colors"/>
    </div>
</template>

<script>
    import KGauge from '@kagronick/kgauge-vue'

    export default {
        name: 'StatisticsGauge',
        components: {
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
                loading: false,
                colors: [
                    '#FF0000',
                    '#62F416'
                ]
            }
        },
        mounted() {
            this.loading = true
            this.getValue()
        },
        methods: {
            async getValue() {
                const response = await fetch(this.getValueUrl)
                const json = await response.json()
                this.value = json.totalUsage * 100
                //this.loading = false
            }
        }
    }
</script>
