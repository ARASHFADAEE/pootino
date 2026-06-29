export function liveListing(initialSearch = '') {
    return {
        search: initialSearch,
        timer: null,
        loading: false,

        onSearchInput() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.refreshResults(), 500);
        },

        buildParams() {
            const params = new URLSearchParams(window.location.search);
            params.set('search', this.search.trim());
            params.delete('page');
            return params;
        },

        syncUrl(params) {
            const query = params.toString();
            const next = query ? `${window.location.pathname}?${query}` : window.location.pathname;
            window.history.replaceState({}, '', next);
        },

        async refreshResults() {
            const params = this.buildParams();
            params.set('partial', '1');
            this.loading = true;

            try {
                const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('search failed');
                }

                const data = await response.json();
                const container = this.$refs.results;

                if (container) {
                    container.innerHTML = data.html;
                    this.$nextTick(() => window.Alpine?.initTree(container));
                }

                this.syncUrl(this.buildParams());
            } catch {
                // keep current results on failure
            } finally {
                this.loading = false;
            }
        },
    };
}
