import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import { jalaliDatePicker } from './jalali/datepicker.js';
import { infiniteAds } from './infinite-ads.js';

window.Alpine = Alpine;
Alpine.data('jalaliDatePicker', jalaliDatePicker);
Alpine.data('infiniteAds', infiniteAds);
Alpine.start();

const instances = new WeakMap();

const bootSearchableSelects = () => {
    document.querySelectorAll('select[data-searchable]').forEach((el) => {
        if (instances.has(el)) {
            return;
        }

        const instance = new TomSelect(el, {
            create: false,
            maxOptions: 5000,
            sortField: { field: 'text', direction: 'asc' },
        });

        instances.set(el, instance);
    });
};

const filterCityByProvince = (provinceSelect, citySelect) => {
    const selectedProvince = provinceSelect.value;
    const currentValue = citySelect.value;
    const options = [...citySelect.options];

    options.forEach((option) => {
        if (!option.value) {
            option.hidden = false;
            return;
        }

        const belongs = option.dataset.provinceId === selectedProvince;
        option.hidden = selectedProvince ? !belongs : false;
    });

    if (selectedProvince) {
        const stillValid = options.some((opt) => opt.value === currentValue && !opt.hidden);
        if (!stillValid) {
            citySelect.value = '';
        }
    }

    const ts = instances.get(citySelect);
    if (ts) {
        ts.clearOptions();
        options.filter((opt) => !opt.hidden).forEach((opt) => {
            ts.addOption({ value: opt.value, text: opt.text });
        });
        ts.refreshOptions(false);
        ts.setValue(citySelect.value || '', true);
    }
};

const bootDependentSelects = () => {
    document.querySelectorAll('[data-province-select]').forEach((provinceSelect) => {
        const targetName = provinceSelect.dataset.provinceSelect;
        const citySelect = document.querySelector(`[data-city-select="${targetName}"]`);
        if (!citySelect) return;

        filterCityByProvince(provinceSelect, citySelect);
        provinceSelect.addEventListener('change', () => filterCityByProvince(provinceSelect, citySelect));
    });
};

document.addEventListener('DOMContentLoaded', () => {
    bootSearchableSelects();
    bootDependentSelects();
});
