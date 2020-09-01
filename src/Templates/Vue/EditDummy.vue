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
      :disabled="$store.getters.loading[`put:api/dummykcs/${editedDummy.id}`]"
      :errors="dummyValidationErrors"
      :dummykc="editedDummy"
      @clear:errors="clearDummyValidationErrors"
      @cancel="goToDummysPage"
      @save="onSave"
    />
  </v-dialog>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import store from '@/store';
import DummyForm from '@/components/DummyForm';

export default {
  name: 'EditDummy',

  components: { DummyForm },

  computed: mapState('dummys', ['editedDummy', 'dummyValidationErrors', 'dummyFilterParams']),

  beforeRouteEnter(to, from, next) {
    store
      .dispatch('dummys/editDummy', +to.params.id)
      .then(() => {
        next();
      })
      .catch(() => {
        next({ name: 'dummys', query: store.state.dummys.dummyFilterParams });
      });
  },

  methods: {
    ...mapActions('dummys', ['updateDummy', 'clearDummyValidationErrors']),

    onSave(dummy) {
      this.updateDummy(dummy).then(() => {
        this.$router.push({ name: 'dummys', query: this.dummyFilterParams });
      });
    },

    goToDummysPage() {
      this.$router.push({ name: 'dummys', query: this.dummyFilterParams });
    },
  },
};
</script>
