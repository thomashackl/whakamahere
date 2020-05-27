export const globalfunctions = {
    methods: {
        getWeekdays: function() {
            return [
                { number: 1, name: 'Montag' },
                { number: 2, name: 'Dienstag' },
                { number: 3, name: 'Mittwoch' },
                { number: 4, name: 'Donnerstag' },
                { number: 5, name: 'Freitag' },
                { number: 6, name: 'Samstag' },
                { number: 0, name: 'Sonntag' }
            ]
        },
        showErrorMessage: function(error) {
            let messagebox = document.createElement('div')
            messagebox.classList.add('messagebox')
            messagebox.classList.add('messagebox_error')
            messagebox.innerHTML = error.statusText

            STUDIP.Dialog.show(messagebox, { height: 250, width: 400, title: 'Fehler (' + error.status + ')' })
        }
    }
}
