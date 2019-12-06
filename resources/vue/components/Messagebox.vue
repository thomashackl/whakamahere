<template>
    <div :class="'messagebox messagebox_' + type">
        <div class="messagebox_buttons">
            <a class="close" href="#" title="Nachrichtenbox schließen">
                <span>Nachrichtenbox schließen</span>
            </a>
        </div>
        <div>
            {{ message }}
        </div>
        <div v-if="details.length > 0">
            <ul>
                <li v-for="(detail, index) in details" :key="index">
                    {{ detail }}
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Messagebox',
        props: {
            type: {
                type: String,
                default: 'info'
            },
            message: {
                type: String,
                default: ''
            },
            details: {
                type: Array,
                default: []
            }
        }
    }
</script>
