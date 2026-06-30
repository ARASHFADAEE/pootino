import { toEnDigits, toFaDigits } from './jalali/calendar.js';

export function persianNumericInput(initialValue = '', maxLength = null) {
    let latin = toEnDigits(String(initialValue ?? '')).replace(/\D/g, '');
    if (maxLength) {
        latin = latin.slice(0, maxLength);
    }

    return {
        display: toFaDigits(latin),
        latin,

        init() {
            this.syncHidden();
        },

        onInput() {
            let raw = toEnDigits(this.display).replace(/\D/g, '');
            if (maxLength) {
                raw = raw.slice(0, maxLength);
            }

            this.latin = raw;
            this.display = toFaDigits(raw);
            this.syncHidden();
        },

        syncHidden() {
            if (this.$refs.hidden) {
                this.$refs.hidden.value = this.latin;
            }
        },
    };
}
