<template>
    <section>
        <label for="room">
            Raum
        </label>
        <select2 :options="myRooms" :id="'room'" :name="'room'" :value="selectedRoom" @change="onChange($event)"></select2>
    </section>
</template>

<script>
    import bus from 'jsassets/bus'
    import Select2 from './Select2'

    export default {
        name: 'RoomFilter',
        components: {
            Select2
        },
        props: {
            rooms: Array,
            selectedRoom: String
        },
        data() {
            return {
                selected: this.selectedRoom
            }
        },
        methods: {
            onChange: function(value) {
                bus.$emit('updated-room', value)
            }
        },
        computed: {
            myRooms: function() {
                let options = [{
                    id: '',
                    text: '-- alle --',
                    children: []
                }]

                for (const building in this.rooms) {
                    options.push({
                        id: this.rooms[building].id,
                        text: this.rooms[building].text,
                        children: this.rooms[building].children
                    })
                }

                return options
            }
        }
    }
</script>
