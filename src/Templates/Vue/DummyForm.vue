<template>
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

      <v-spacer />

      <v-btn
        color="primary"
        text
        @click.native="$emit('cancel')"
      >
        {{ $t('cancel') }}
      </v-btn>

      <v-btn
        :disabled="disabled"
        :loading="disabled"
        color="primary"
        text
        @click="$emit('save', item)"
      >
        {{ $t('save') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>VUE_FORM_IMPORTS
import clone from 'just-clone';
import formMixin from '@/mixins/form-mixin';

export default {
  name: 'DummyForm',

  components: {
    VUE_FORM_COMPONENTS},

  mixins: [formMixin],

  props: {
    dummy: {
      type: Object,
      required: true,
    },
    errors: {
      type: Object,
      default: () => ({}),
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      item: {},
      VUE_FORM_DATA_ATTRIBUTES};
  },

  created() {
    this.item = clone(this.dummy);
  },

  computed: {
    formTitle() {
      return this.$t(this.dummy.id ? 'edit_dummysc' : 'new_dummysc');
    },
  },
};
</script>
