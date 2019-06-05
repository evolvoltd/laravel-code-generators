<template>
  <div class="full-width">
    <h1 class="pa-3 display-1">
      {{ $t('dummys') }}
    </h1>

    <DummyTable
      v-if="!isDataLoading"
      :rows="dummyArray"
      :pagination="dummyPagination"
      @rowClick="openDummyInForm"
      @delete="deleteDummy"
      @changePage="getPaginatedDummys"
    />

    <v-dialog
      v-if="isDummyFormOpen"
      v-model="isDummyFormOpen"
      :fullscreen="$vuetify.breakpoint.xsOnly"
      max-width="800px"
      class="modal-container"
      persistent
      scrollable>
      <DummyForm
        :dummy="dummyFormItem"
        :errors="dummyFormErrors"
        :is-saving-disabled="isRequestPending"
        @save="onDummyFormSave"
        @cancel="onDummyFormCancel"
      />
    </v-dialog>

    <v-scale-transition>
      <v-btn
        v-if="!isDataLoading"
        color="primary"
        bottom
        dark
        fab
        fixed
        right
        @click.stop="onPrimaryButtonClick">
        <v-icon>add</v-icon>
      </v-btn>
    </v-scale-transition>
  </div>
</template>

<script>
  import DummyForm from '../components/DummyForm';
  import DummyTable from '../components/DummyTable';
  import crudMixin from '../mixins/crud-mixin';
  import { dummyService } from '../api/dummy-service';

  export default {
    name: 'DummysPage',

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
        dummyFormErrors: {},
        dummyFilterParams: '',
        isDummyFormOpen: false,

        isDataLoading: true,
        isRequestPending: false,
      };
    },

    created() {
      this.getPaginatedDummys(1);
    },

    methods: {
      getPaginatedDummys(pageNo) {
        this.crudMixin_getPage(dummyService.getPage, dummyService.model, pageNo, this.dummyFilterParams);
      },

      onPrimaryButtonClick() {
        this.dummyFormItem = {};
        this.dummyFormErrors = {};
        this.isDummyFormOpen = true;
      },

      openDummyInForm(dummy) {
        this.dummyFormItem = JSON.parse(JSON.stringify(dummy));
        this.dummyFormErrors = {};
        this.isDummyFormOpen = true;
      },

      async onDummyFormSave(dummy) {
        await this.crudMixin_createOrUpdate(dummyService, dummy);
      },

      onDummyFormCancel() {
        this.isDummyFormOpen = false;
      },

      deleteDummy() {
        this.crudMixin_delete(dummyService.delete, dummyService.model, dummy);
      },
    },
  };
</script>
