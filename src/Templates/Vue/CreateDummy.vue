<template>
  <v-dialog
    :value="true"
    :fullscreen="$vuetify.breakpoint.xsOnly"
    transition="slide-y-reverse-transition"
    max-width="800"
    scrollable
    persistent
  >
    <DummyForm
      :dummykc="newDummy"
      :errors="dummyValidationErrors"
      :disabled="$store.getters.loading['post:api/dummykcs']"
      @clear:errors="CLEAR_DUMMY_VALIDATION_ERRORS"
      @cancel="goBack"
      @save="onSave"
    />
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex';
import DummyForm from '@/components/DummyForm';

export default {
  name: 'CreateDummy',

  components: { DummyForm },

  computed: mapState('dummys', ['newDummy', 'dummyValidationErrors', 'dummyFilterParams']),

  created() {
    this.SET_DUMMY_VALIDATION_ERRORS({});
  },

  methods: {
    ...mapActions('dummys', ['storeDummy']),
    ...mapMutations('dummys', ['SET_DUMMY_VALIDATION_ERRORS', 'CLEAR_DUMMY_VALIDATION_ERRORS']),

    onSave(dummy) {
      this.storeDummy(dummy).then(() => {
        this.goToDummysPage();
      });
    },

    goToDummysPage() {
      this.$router.push({ name: 'dummys', query: this.dummyFilterParams });
    },
  },
};
</script>
