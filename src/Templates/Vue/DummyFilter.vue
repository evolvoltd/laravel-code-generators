<template>
  <v-container class="py-0" fluid>
    <v-row>
      <v-col cols="12" sm="4" md="3">
        <v-text-field
          :value="filterParams.search"
          :label="$t('search')"
          prepend-inner-icon="mdi-magnify"
          clearable
          @keyup.enter="$event.target.blur()"
          @input="filterMixin_handleDebounce('search', $event)"
        />
      </v-col>
<!--      <v-col cols="12" sm="8" md="9" class="d-flex align-center justify-end">-->
<!--        <v-btn color="primary" text @click="filterMixin_resetFilter">-->
<!--          <v-icon left>mdi-close</v-icon>-->
<!--          {{ $t('reset_filters') }}-->
<!--        </v-btn>-->
<!--      </v-col>-->
    </v-row>
  </v-container>
</template>

<script>
import filterMixin from '@/mixins/filter-mixin';

export default {
  name: 'DummyFilter',

  mixins: [filterMixin],
};
</script>

<style scoped>

</style>
