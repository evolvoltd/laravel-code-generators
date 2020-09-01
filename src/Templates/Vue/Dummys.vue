<template>
  <div class="page-wrapper">
    <div class="d-flex align-center pa-4">
      <h1 class="text-h6">
        {{ $t('dummyscs') }}
      </h1>
      <v-spacer />
      <BasePrimaryActionButton
        :label="$t('create_dummysc')"
        @click="$router.push({ name: 'createDummy' })"
      />
    </div>

    <BaseTableLoader :loading="!dummys">
      <DummyTable
        :items="dummys"
        :loading="$store.getters.loading['get:api/dummykcs']"
        :pagination="dummyPagination"
        @delete="deleteDummy"
        @edit="$router.push({ name: 'editDummy', params: { id: $event.id } })"
        @update:page="onPageChange"
      />
    </BaseTableLoader>

    <router-view/>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import BaseTableLoader from '@/components/base/BaseTableLoader';
import BasePrimaryActionButton from '@/components/base/BasePrimaryActionButton';
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
