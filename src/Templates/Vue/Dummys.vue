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

    <DummyFilter
      :filter-params="dummyFilterParams"
      @change="$router.push({ name: 'dummys', query: $event })"
      @reset="resetDummyFilters"
    />

    <DummyTable
      :items="dummys"
      :loading="$store.getters.loading['get:api/dummykcs']"
      :pagination="dummyPagination"
      @delete="deleteDummy"
      @edit="$router.push({ name: 'editDummy', params: { id: $event.id } })"
      @update:page="onPageChange"
    />

    <router-view />
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import BasePrimaryActionButton from '@/components/base/BasePrimaryActionButton';
import DummyTable from '@/components/tables/DummyTable';
import DummyFilter from '@/components/filters/DummyFilter';
import { getDefaultDummyFilterParams } from '@/store/modules/dummykcs-module';

export default {
  name: 'Dummys',

  components: {
    BasePrimaryActionButton,
    DummyTable,
    DummyFilter,
  },

  computed: {
    ...mapState('dummys', ['dummys', 'dummyPagination', 'dummyFilterParams']),
  },

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

    resetDummyFilters() {
      const defaultFilters = getDefaultDummyFilterParams();
      if (JSON.stringify(defaultFilters) === JSON.stringify(this.dummyFilterParams)) {
        return;
      }
      this.$router.push({ name: 'dummys', query: defaultFilters });
    },
  },
};
</script>
