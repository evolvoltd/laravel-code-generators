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
      @clear:errors="CLEAR_DUMMY_VALIDATION_ERRORS"
      @cancel="goToDummysPage"
      @save="onSave"
    />
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex';
import store from '@/store';
import DummyForm from '@/components/forms/DummyForm';

export default {
  name: 'EditDummy',

  components: { DummyForm },

  computed: {
    ...mapState('dummys', ['editedDummy', 'dummyValidationErrors', 'dummyFilterParams']),
  },

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
    ...mapActions('dummys', ['updateDummy']),
    ...mapMutations('dummys', ['CLEAR_DUMMY_VALIDATION_ERRORS']),

    onSave(dummy) {
      this.updateDummy(dummy).then(() => {
        this.goToDummysPage();
      });
    },

    goToDummysPage() {
      this.$router.push({ name: 'dummys', query: this.dummyFilterParams });
    },
  },
};
</script>
