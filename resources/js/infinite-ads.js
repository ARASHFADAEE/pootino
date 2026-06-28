export function infiniteAds(nextPageUrl = null) {
    return {
        nextUrl: nextPageUrl,
        loading: false,
        hasMore: !!nextPageUrl,
        observer: null,

        init() {
            if (!this.nextUrl) {
                return;
            }

            this.observer = new IntersectionObserver(
                (entries) => {
                    if (entries[0]?.isIntersecting) {
                        this.loadMore();
                    }
                },
                { rootMargin: '240px' },
            );

            this.$nextTick(() => {
                if (this.$refs.sentinel) {
                    this.observer.observe(this.$refs.sentinel);
                }
            });
        },

        async loadMore() {
            if (!this.nextUrl || this.loading) {
                return;
            }

            this.loading = true;

            try {
                const url = new URL(this.nextUrl, window.location.origin);
                url.searchParams.set('infinite', '1');

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('load failed');
                }

                const data = await response.json();
                const grid = this.$el.querySelector('.grid');

                if (grid && data.html) {
                    grid.insertAdjacentHTML('beforeend', data.html);
                }

                this.nextUrl = data.next_page_url;
                this.hasMore = data.has_more;

                if (!this.hasMore) {
                    this.observer?.disconnect();
                }
            } catch {
                this.hasMore = false;
                this.observer?.disconnect();
            } finally {
                this.loading = false;
            }
        },
    };
}
