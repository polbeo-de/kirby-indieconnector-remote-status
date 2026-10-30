panel.plugin('polbeo/kirby-indieconnector-remote-status', { components: {
  'k-remote-webmentions-view': {
    data: () => ({ data: null, sources: [], selectedSourceId: null, loading: false }),
    mounted() { this.loadSources(); },
    methods: { async loadSources() { try { this.sources = await this.$panel.api.get('indieconnector-remote-status/sources'); this.selectedSourceId = this.sources[0]?.id ?? null; await this.load(); } catch (_) { this.$panel.notification.error(this.$t('polbeo.indie.remote.unavailable')); } }, async load() { if (!this.selectedSourceId) return; this.loading = true; try { this.data = await this.$panel.api.get(`indieconnector-remote-status/${this.selectedSourceId}/status`); } catch (_) { this.$panel.notification.error(this.$t('polbeo.indie.remote.unavailable')); } finally { this.loading = false; } }, table(h, title, rows, columns) { return h('div', [h('k-headline', { props: { tag: 'h2' } }, [title]), h('k-table', { props: { columns, rows, empty: 'No entries found.' } })]); } },
    render(h) {
      const d = this.data?.data, t = (key) => this.$t(`polbeo.indie.remote.${key}`);
      const button = h('button', { attrs: { type: 'button', disabled: this.loading }, on: { click: this.load } }, [t('refresh')]);
      const sourceSelect = h('select', { domProps: { value: this.selectedSourceId }, on: { change: async (event) => { this.selectedSourceId = event.target.value; await this.load(); } } }, this.sources.map((source) => h('option', { domProps: { value: source.id } }, [source.label])));
      if (!d) return h('k-panel-inside', [h('div', [h('k-headline', { props: { tag: 'h1' } }, [t('title')]), sourceSelect, button])]);
      const summary = Object.entries(d.summary).map(([label, value]) => ({ label, value }));
      return h('k-panel-inside', [h('div', [h('k-headline', { props: { tag: 'h1' } }, [t('title')]), sourceSelect, button, h('p', [`Queue: ${d.itemsInQueue}`]),
        this.table(h, 'Summary', summary, { label: { label: 'Type', type: 'text' }, value: { label: 'Count', type: 'text' } }),
        this.table(h, 'Target pages', d.targets, { title: { label: 'Page', type: 'text' }, sum: { label: 'Count', type: 'text' } }),
        this.table(h, 'Sources', d.sources, { sourceType: { label: 'Source', type: 'text' }, sum: { label: 'Count', type: 'text' } }),
        this.table(h, 'Authors', d.authors, { author: { label: 'Author', type: 'text' }, sourceType: { label: 'Source', type: 'text' }, likes: { label: 'Likes', type: 'text' }, replies: { label: 'Replies', type: 'text' } }),
        this.table(h, 'Sent webmentions', d.sent.map((item) => ({ page: item.page?.title || '—', entries: item.entries?.length || 0 })), { page: { label: 'Page', type: 'text' }, entries: { label: 'Count', type: 'text' } }),
      ])]);
    },
  },
}});
