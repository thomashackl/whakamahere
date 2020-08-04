<template>
    <section>
        <label for="searchterm">
            Veranstaltungsnummer oder Name
        </label>
        <input type="text" name="searchterm" id="searchterm" v-model="search"
               placeholder="Suchbegriff mit mehr als drei Zeichen" @change="doSearch" @keypress="checkEnter">
    </section>
</template>

<script>
    import bus from 'jsassets/bus'
    import { globalfunctions } from '../mixins/globalfunctions'

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
            doSearch: function(event) {
                bus.$emit('updated-searchterm', this.search)
            },
            checkEnter: function(event) {
                if (event.which == 13) {
                    event.preventDefault()
                    this.doSearch(event)
                }
            }
        }
    }
</script>
