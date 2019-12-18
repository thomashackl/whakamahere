<template>
    <div>
        <vue-element-loading :active="loading" spinner="bar-fade-scale" color="#28487C" size="75" duration="1.5"/>
        <k-gauge v-if="!loading" title="Raumauslastung" :value="value" :min="min" :max="max"
                 :format-function="formatPct"/>
    </div>
</template>

<script>
    import VueElementLoading from 'vue-element-loading'
    import KGauge from '@kagronick/kgauge-vue'

    export default {
        name: 'StatisticsGauge',
        components: {
            VueElementLoading,
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
            this.getValue()
        },
        methods: {
            async getValue() {
                fetch(this.getValueUrl)
                    .then((response) => {
                        response.json().then((json) => {
                            this.loading = false
                            this.value = json.totalUsage * 100
                        })
                    })
            }
        }
    }
</script>
