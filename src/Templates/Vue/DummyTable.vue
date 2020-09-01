<template>
  <v-data-table
    :expanded="tableMixin_expandedRows"
    :footer-props="{
      'items-per-page-options': [pagination.per_page],
    }"
    :headers="tableMixin_displayedHeaders"
    :items="items"
    :mobile-breakpoint="0"
    :page="pagination.current_page"
    :server-items-length="pagination.total"
    :class="{ 'content-loading': loading }"
    disable-sort
    @click:row="tableMixin_onRowClick"
    @update:page="tableMixin_changePage"
  >
    <template v-slot:item.actions="{ item }">
      <BaseActionMenu
        :actions="getRowActions(item)"
        :loading="$store.getters.loading[`delete:api/dummykcs/${item.id}`]"
        :item="item"
      />
    </template>

    <template v-slot:expanded-item="{ headers, item }">
      <BaseExpandedTableRow
        :colspan="tableMixin_displayedHeaders.length"
        :headers="tableMixin_hiddenHeaders"
        :item="item"
      />
    </template>
  </v-data-table>
</template>

<script>
import BaseActionMenu from '@/components/base/BaseActionMenu';
import BaseExpandedTableRow from '@/components/base/BaseExpandedTableRow';
import tableMixin from '@/mixins/table-mixin';

export default {
  name: 'DummyTable',

  components: {
    BaseExpandedTableRow,
    BaseActionMenu,
  },

  mixins: [tableMixin],

  props: {
    items: {
      type: Array,
      required: true,
    },
    pagination: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },

  computed: {
    headers() {
      return [
        VUE_TABLE_HEADERS];
    },
  },

  methods: {
    getRowActions() {
      return [
        {
          callback: (p) => this.$emit('edit', p),
          label: this.$t('edit'),
          icon: 'mdi-pencil',
        },
        {
          callback: (p) => this.$emit('delete', p),
          label: this.$t('delete'),
          icon: 'mdi-delete',
        },
      ];
    },
  },
};
</script>
