<template>
  <BaseTableLoader :loading="loading">
    <v-data-table
      :expanded="tableMixin_expandedRows"
      :footer-props="{
        'items-per-page-options': [pagination.rowsPerPage],
      }"
      :headers="tableMixin_displayedHeaders"
      :items="rows"
      :mobile-breakpoint="0"
      :page="pagination.page"
      :server-items-length="pagination.totalItems"
      disable-sort
      @click:row="tableMixin_onRowClick"
      @update:page="tableMixin_changePage"
    >
      <template v-slot:top>
        <BaseTableHeader
          :title="$t('dummys')"
          :create-button="$t('create_dummy')"
          @new-item="$emit('new-item')"
        />
      </template>

      <template v-slot:item.actions="{ item }">
        <BaseActionMenu
          :actions="actions"
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
  </BaseTableLoader>
</template>

<script>
import BaseActionMenu from './base/BaseActionMenu';
import BaseTableHeader from './base/BaseTableHeader';
import BaseExpandedTableRow from './base/BaseExpandedTableRow';
import BaseTableLoader from './base/BaseTableLoader';
import tableMixin from '../mixins/table-mixin';

export default {
  name: 'DummyTable',

  components: {
    BaseTableLoader,
    BaseExpandedTableRow,
    BaseTableHeader,
    BaseActionMenu,
  },

  mixins: [tableMixin],

  props: {
    rows: Array,
    pagination: Object,
    loading: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      actions: [
        {
          callback: p => this.$emit('edit', p),
          label: this.$t('edit'),
          icon: 'mdi-pencil',
        },
        {
          callback: p => this.$emit('delete', p),
          label: this.$t('delete'),
          icon: 'mdi-delete',
        },
      ],
      headers: [
        VUE_TABLE_HEADERS],
    };
  },
};
</script>
