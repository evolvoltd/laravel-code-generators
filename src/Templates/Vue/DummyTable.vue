<template>
  <v-data-table
    :headers="headers"
    :items="rows"
    :pagination="pagination"
    :total-items="pagination.totalItems"
    :rows-per-page-items="[pagination.rowsPerPage]"
    :hide-actions="pagination.totalItems <= pagination.rowsPerPage"
    disable-initial-sort
    @update:pagination="tableMixin_changePage">
    <template slot="headers" slot-scope="props">
      <th
        v-for="header in headers"
        v-if="!$vuetify.breakpoint[header.hidden]"
        :key="header.text"
        class="text-xs-left">
        {{ header.text }}
      </th>
    </template>

    <template slot="items" slot-scope="props">
      <tr class="table-row clickable" @click="tableMixin_onRowClick(props)">
        VUE_TABLE_COLUMNS
        <td v-if="isTouchDevice" class="actions-column">
          <v-menu bottom lazy left offset-y @click.native.stop>
            <v-btn
              slot="activator"
              icon
              class="mx-0">
              <v-icon>more_vert</v-icon>
            </v-btn>

            <v-list>
              <v-list-tile @click="$emit('rowClick', props)">
                <v-list-tile-action>
                  <v-icon>edit</v-icon>
                </v-list-tile-action>
                <v-list-tile-title>
                  {{ $t('edit') }}
                </v-list-tile-title>
              </v-list-tile>

              <v-list-tile @click="$emit('delete', props)">
                <v-list-tile-action>
                  <v-icon>delete</v-icon>
                </v-list-tile-action>
                <v-list-tile-title>
                  {{ $t('delete') }}
                </v-list-tile-title>
              </v-list-tile>
            </v-list>
          </v-menu>
        </td>

        <td v-else>
          <v-tooltip bottom lazy>
            <v-btn
              slot="activator"
              icon
              class="mx-0"
              @click.stop="$emit('delete', props)">
              <v-icon>delete</v-icon>
            </v-btn>
            <span>
              {{ $t('delete' )}}
            </span>
          </v-tooltip>
        </td>
      </tr>
    </template>

    <template slot="expand" slot-scope="props">
      <div class="row-detail-wrapper">
        VUE_TABLE_ROW_DETAILS</div>
    </template>

    <template slot="no-data">
      <div class="pa-3 text-xs-center">
        <span>
          {{ $t('no_entries') }}
        </span>
      </div>
    </template>
  </v-data-table>
</template>

<script>
import tableMixin from '../mixins/table-mixin';

export default {
  name: 'DummyTable',

  mixins: [tableMixin],

  props: {
    rows: Array,
    pagination: Object,
  },

  computed: {
    isTouchDevice() {
      return this.$store.state.settings.isTouchDevice;
    },

    headers() {
      VUE_TABLE_HEADERS
      if (this.isTouchDevice) {
        headers.push({ text: '' });
      }
      return headers;
    },
  },
};
</script>
