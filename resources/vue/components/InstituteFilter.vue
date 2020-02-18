<template>
    <section>
        <label for="institute">
            Einrichtung
        </label>
        <select2 v-model="selected" :options="myInstitutes" id="institute" name="institute"
                 @change="onChange($event)"></select2>
    </section>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'InstituteFilter',
        props: {
            institutes: Array,
            selectedInstitute: String
        },
        data() {
            return {
                selected: this.selectedInstitute
            }
        },
        computed: {
            myInstitutes: function() {
                let options = [{
                    id: '',
                    text: '-- alle --',
                    children: []
                }]

                for (let i = 0 ; i < this.institutes.length ; i++) {
                    options.push({
                        id: this.institutes[i].Institut_id,
                        text: this.institutes[i].is_fak == 1 ?
                            this.institutes[i].Name :
                            '\xa0\xa0' + this.institutes[i].Name,
                        children: []
                    })

                    if (this.institutes[i].is_fak == 1) {
                        options.push({
                            id: this.institutes[i].Institut_id + '+sub',
                            text: '\xa0\xa0[alle unter ' + this.institutes[i].Name + ']',
                            children: []
                        })
                    }
                }
                return options
            }
        },
        methods: {
            onChange: function(value) {
                bus.$emit('update-institute', value)
            }
        }
    }
</script>
