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
          :disabled="requestPending"
          :loading="requestPending"
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
import { mapActions } from 'vuex';
import formMixin from '../mixins/form-mixin';

export default {
  name: 'DummyForm',

  mixins: [formMixin],

  data() {
    return {
      model: 'dummy',
      backendErrors: {
        VUE_ERROR_FIELDS
      },
    };
  },

  computed: {
    formTitle() {
      return this.index === -1 ? this.$t('new_dummy') : this.$t('edit_dummy');
    },
  },

  methods: {
    ...mapActions({
      create: 'dummy/create',
      update: 'dummy/update',
    }),
  },
};
</script>
