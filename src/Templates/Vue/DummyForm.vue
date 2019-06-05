<template>
  <v-form @submit.prevent="save" ref="form">
    <v-card>
      <v-card-text>
        <v-container grid-list-md class="pa-2">
          <v-layout wrap>
            <v-flex class="form-group-header" xs12>
              <h2 class="headline">
                {{ formTitle }}
              </h2>
            </v-flex>

            VUE_FORM_FIELDS

          </v-layout>
        </v-container>
      </v-card-text>

      <v-card-actions>
        <v-spacer/>
        <v-btn
          color="primary"
          flat
          @click.native="$emit('cancel')">
          {{ $t('cancel') }}
        </v-btn>

        <v-btn
          :disabled="isSavingDisabled"
          :loading="isSavingDisabled"
          type="submit"
          color="primary"
          flat>
          {{ $t('save') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<script>
import formMixin from '../mixins/form-mixin';

export default {
  name: 'DummyForm',

  mixins: [formMixin],

  props: {
    dummy: Object,
    errors: Object,
    isSavingDisabled: Boolean,
  },

  data() {
    return {
      VUE_DATE_PICKER_ATTRIBUTES
    };
  },

  computed: {
    formTitle() {
      return this.dummy.id ? this.$t('new_dummy') : this.$t('edit_dummy');
    },
  },
};
</script>
