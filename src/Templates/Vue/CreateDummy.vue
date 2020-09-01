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
      @clear:errors="clearDummyValidationErrors"
      @cancel="goToDummysPage"
      @save="onSave"
    />
  </v-dialog>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import DummyForm from '@/components/DummyForm';

export default {
  name: 'CreateDummy',

  components: { DummyForm },

  computed: mapState('dummys', ['newDummy', 'dummyValidationErrors', 'dummyFilterParams']),

  methods: {
    ...mapActions('dummys', ['storeDummy', 'clearDummyValidationErrors']),

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
