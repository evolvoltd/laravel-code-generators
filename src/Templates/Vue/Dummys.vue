<template>
  <div class="page-wrapper">
    <DummyTable
      :rows="dummyArray"
      :pagination="dummyPagination"
      :loading="isDataLoading"
      @change-page="getPaginatedDummys"
      @delete="crudMixin_delete(onDelete, 'dummy', $event)"
      @edit="crudMixin_openForm('dummy', $event)"
    />

    <v-dialog
      v-model="isDummyFormOpen"
      :fullscreen="$vuetify.breakpoint.xsOnly"
      transition="slide-y-reverse-transition"
      max-width="800"
      persistent
      scrollable
    >
      <DummyForm
        :dialog="isDiscountFormOpen"
        :dummykc="dummyFormItem"
        @create="crudMixin_created('dummy', $event)"
        @update="crudMixin_updated('dummy', $event)"
        @cancel="isDiscountFormOpen = false"
      />
    </v-dialog>

    <v-scale-transition>
      <v-btn
        v-if="!isDataLoading && $vuetify.breakpoint.xsOnly"
        color="primary"
        bottom
        dark
        fab
        fixed
        right
        @click.stop="crudMixin_openForm('dummy')"
      >
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-scale-transition>
  </div>
</template>

<script>
import DummyForm from '../components/DummyForm';
import DummyTable from '../components/DummyTable';
import crudMixin from '../mixins/crud-mixin';
import { dummyService } from '../api/dummykc-service';

export default {
  name: 'Dummys',

  components: {
    DummyForm,
    DummyTable,
  },

  mixins: [crudMixin],

  data() {
    return {
      dummyArray: [],
      dummyPagination: {
        page: 1,
      },
      dummyFormItem: {},
      dummyFilterParams: '',
      isDummyFormOpen: false,

      isDataLoading: true,
      onDelete: dummyService.delete,
    };
  },

  created() {
    this.getPaginatedDummys(1);
  },

  methods: {
    getPaginatedDummys(pageNo) {
      this.crudMixin_getPage(
        dummyService.getPage,
        dummyService.model,
        pageNo,
        this.dummyFilterParams
      );
    },
  },
};
</script>
