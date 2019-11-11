<template>
  <div class="page-wrapper">
    <DummyTable
      :loading="isDataLoading"
      :pagination="dummyPagination"
      :rows="dummyArray"
      @change-page="getPaginatedDummys"
      @edit="crudMixin_openForm('dummy', $event)"
      @delete="crudMixin_delete(onDelete, 'dummy', $event)"
      @new-item="crudMixin_openForm('dummy', newDummyTemplate)"
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
        :dialog="isDummyFormOpen"
        :form-item="dummyFormItem"
        @create="crudMixin_created('dummy', $event)"
        @update="crudMixin_updated('dummy', $event)"
        @cancel="isDummyFormOpen = false"
      />
    </v-dialog>
  </div>
</template>

<script>
import DummyForm from '../components/DummyForm';
import DummyTable from '../components/DummyTable';
import crudMixin from '../mixins/crud-mixin';
import dummyService from '../api/dummykc-service';

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
      newDummyTemplate: {},
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
        this.dummyFilterParams,
      );
    },
  },
};
</script>
