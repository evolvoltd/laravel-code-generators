<template>
  <v-form @submit.prevent="onSubmit">
    <v-card>
      <v-card-title>
        {{ formTitle }}
      </v-card-title>

      <v-card-text>
        <v-row dense>
          VUE_FORM_FIELDS</v-row>
      </v-card-text>

      <v-card-actions>
        <span v-if="!dummy.id" class="subtitle-2 ml-3">
          * {{ $t('must_be_filled') }}
        </span>

        <v-spacer/>

        <v-btn
          color="primary"
          text
          @click.native="$emit('cancel')"
        >
          {{ $t('cancel') }}
        </v-btn>

        <v-btn
          :disabled="isRequestPending"
          :loading="isRequestPending"
          type="submit"
          color="primary"
          text
        >
          {{ $t('save') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<script>
VUE_FORM_IMPORTS
import crudMixin from '../../mixins/crud-mixin';
import dialogMixin from '../../mixins/dialog-mixin';
import formMixin from '../../mixins/form-mixin';
import dummyService from '../../api/dummy-service';

export default {
  name: 'DummyForm',

  components: {
    VUE_FORM_COMPONENTS},

  mixins: [crudMixin, dialogMixin, formMixin],

  props: {
    formItem: Object,
  },

  data() {
    return {
      errors: {},
      isRequestPending: false,
      dummy: {},
      VUE_FORM_DATA_ATTRIBUTES};
  },

  computed: {
    formTitle() {
      return this.$t(this.dummy.id ? 'edit_dummysc' : 'new_dummysc');
    },
  },

  methods: {
    onDialogOpen() {
      this.dummy = JSON.parse(JSON.stringify(this.formItem));
      this.errors = {};
    },

    onSubmit() {
      this.crudMixin_createOrUpdate(dummyService, this.dummy);
    },
  },
};
</script>
