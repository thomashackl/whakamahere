<template>
    <nav {{ attributeString }}>
        <a class="action-menu-icon" title="Aktionen" aria-expanded="false" aria-label="Aktionsmenü" href="#">
            <div></div>
            <div></div>
            <div></div>
        </a>
        <div class="action-menu-content">
            <div class="action-menu-title">Aktionen</div>
            <ul class="action-menu-list">
                <li v-for="action in actions" class="action-menu-item">
                    <a v-if="action.type == 'link'" :href="action.link" @click="action.click" {{ makeAttributeString(action.attributes) }}>
                        <span v-if="action.icon">action.icon</span>
                        <span v-else class="action-menu-no-icon"></span>
                        {{ action.label }}
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
    export default {
        name: 'StudipActionMenu',
        props: {
            attributes: {
                type: Object,
                default: () => {}
            },
            actions: {
                type: Array,
                default: () => []
            }
        },
        computed: {
            attributeString: function() {
                return this.makeAttributeString(this.attributes)
            }
        },
        methods: {
            makeAttributeString: function(attributes) {
                let string = ''

                for (prop in this.attributes) {
                    string += prop + '="' + this.attributes + '" '
                }

                return string
            }
        }
    }
</script>
