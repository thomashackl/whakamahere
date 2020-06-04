<template>
    <section>
        <label for="searchterm">
            Veranstaltungsnummer oder Name
        </label>
        <input type="text" name="searchterm" id="searchterm" v-model="search"
               placeholder="Suchbegriff mit mehr als drei Zeichen" @change="doSearch">
    </section>
</template>

<script>
    import bus from 'jsassets/bus'
    import { globalfunctions } from './mixins/globalfunctions'

    export default {
        name: 'TextFilter',
        mixins: [
            globalfunctions
        ],
        props: {
            searchterm: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                search: this.searchterm
            }
        },
        methods: {
            doSearch() {
                bus.$emit('updated-searchterm', this.search)
            }
        }
    }
</script>
