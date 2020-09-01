<template>
  <div class="page-wrapper">
    <div class="d-flex align-center pa-4">
      <h1 class="text-h6">
        {{ $t('dummys') }}
      </h1>
      <v-spacer />
      <BasePrimaryActionButton
        :label="$t('create_dummy')"
        @click="$router.push({ name: 'createDummy' })"
      />
    </div>

    <BaseTableLoader :loading="!dummys">
      <DummyTable
        :items="dummys"
        :disabled-item-ids="disabledDummyIds"
        :loading="loadingDummys"
        :pagination="dummyPagination"
        @delete="deleteDummy"
        @edit="$router.push({ name: 'editDummy', params: { id: $event.id } })"
        @update:page="onPageChange"
      />
    </BaseTableLoader>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import BaseTableLoader from '@/components/BaseTableLoader';
import BasePrimaryActionButton from '@/components/BasePrimaryActionButton';
import DummyTable from '@/components/DummyTable';

export default {
  name: 'Dummys',

  components: {
    BaseTableLoader,
    BasePrimaryActionButton,
    DummyTable,
  },

  computed: mapState('dummys', [
    'dummys',
    'dummyPagination',
    'dummyFilterParams',
    'loadingDummys',
    'disableDummyIds',
  ]),

  created() {
    this.fetchDummys(this.$route.query);
  },

  beforeRouteUpdate(to, from, next) {
    if (
      JSON.stringify(this.dummyFilterParams) !== JSON.stringify(to.query) &&
      to.name === 'dummys'
    ) {
      this.fetchDummys(to.query);
    }
    next();
  },

  methods: {
    ...mapActions('dummys', ['fetchDummys', 'deleteDummy']),

    onPageChange(page) {
      const query = { ...this.dummyFilterParams, page };
      this.$router.push({ name: 'dummys', query });
    },
  },
};
</script>
