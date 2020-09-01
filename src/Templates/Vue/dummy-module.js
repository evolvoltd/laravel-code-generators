import Vue from 'vue';
import dummyService from '@/api/dummy-service';
import eventBus, { OPEN_SNACKBAR, openConfirmDialog } from '@/util/event-bus';
import i18n from '@/i18n/i18n-config';
import { updateArrayItem, removeArrayItem } from '@/util/array';
import { mapErrorsToInputs } from '@/util/forms';

const getDefaultDummyFormItem = () => ({});

export const getDefaultDummyFilterParams = () => ({});

const state = {
  dummys: null,
  dummyPagination: {},
  editedDummy: {},
  dummyValidationErrors: {},
  dummyFilterParams: getDefaultDummyFilterParams(),
  newDummy: getDefaultDummyFormItem(),
  loadingDummys: false,
  savingDummy: false,
  disabledDummyIds: {},
};

const getters = {};

const mutations = {
  SET_DUMMYS(state, { data, current_page, per_page, total }) {
    state.dummys = data;
    state.dummyPagination = {
      current_page,
      per_page,
      total,
    };
  },

  SET_FILTER_PARAMS(state, params) {
    state.dummyFilterParams = params;
  },

  SET_EDITED_DUMMY(state, dummy) {
    state.dummyValidationErrors = {};
    state.editedDummy = JSON.parse(JSON.stringify(dummy));
  },

  CLEAR_VALIDATION_ERRORS(state, field) {
    Vue.delete(state.dummyValidationErrors, field);
  },

  STORE_DUMMY(state, dummy) {
    state.dummys = [...state.dummys, dummy];
    state.dummyPagination.total += 1;
    state.dummyValidationErrors = {};
    state.newDummy = getDefaultDummyFormItem();
  },

  UPDATE_DUMMY(state, dummy) {
    state.dummys = updateArrayItem(state.dummys, dummy);
  },

  DELETE_DUMMY(state, dummy) {
    state.dummys = removeArrayItem(state.dummys, dummy);
    state.dummyPagination.total -= 1;
  },

  SET_DUMMY_VALIDATION_ERRORS(state, dummyValidationErrors) {
    state.dummyValidationErrors = dummyValidationErrors;
  },

  SET_LOADING_DUMMYS(state, loading) {
    state.loadingDummys = loading;
  },

  SET_SAVING_DUMMY(state, saving) {
    state.savingDummy = saving;
  },

  DISABLE_DUMMY(state, { id, reason }) {
    Vue.set(state.disabledDummyIds, id, reason);
  },

  ENABLE_DUMMY(state, id) {
    Vue.delete(state.disabledDummyIds, id);
  },
};

const actions = {
  fetchDummys({ commit }, params) {
    commit('SET_LOADING_DUMMYS', true);
    return dummyService
      .getPage(params)
      .then((res) => {
        commit('SET_DUMMYS', res.data);
        if (params.page !== state.dummyFilterParams.page) {
          window.scrollTo(0, 0);
        }
        commit('SET_FILTER_PARAMS', params);
        return res.data;
      })
      .finally(() => {
        commit('SET_LOADING_DUMMYS', false);
      });
  },

  storeDummy({ commit }, dummy) {
    commit('SET_SAVING_DUMMY', true);
    return dummyService
      .create(dummy)
      .then((res) => {
        commit('STORE_DUMMY', res.data);
        eventBus.$emit(OPEN_SNACKBAR, i18n.t('dummy_created'));
        return res.data;
      })
      .catch((err) => {
        commit('SET_DUMMY_VALIDATION_ERRORS', mapErrorsToInputs(err));
        throw err;
      })
      .finally(() => {
        commit('SET_SAVING_DUMMY', false);
      });
  },

  editDummy({ state, commit }, dummyId) {
    const dummy = state.dummys?.find((c) => c.id === dummyId);
    if (dummy) {
      commit('SET_EDITED_DUMMY', dummy);
      return Promise.resolve(dummy);
    }
    return dummyService.getById(dummyId).then((res) => {
      commit('SET_EDITED_DUMMY', res.data);
      return res.data;
    });
  },

  updateDummy({ commit }, dummy) {
    commit('SET_SAVING_DUMMY', true);
    return dummyService
      .update(dummy)
      .then((res) => {
        commit('UPDATE_DUMMY', res.data);
        eventBus.$emit(OPEN_SNACKBAR, i18n.t('dummy_updated'));
        return res.data;
      })
      .catch((err) => {
        commit('SET_DUMMY_VALIDATION_ERRORS', mapErrorsToInputs(err));
        throw err;
      })
      .finally(() => commit('SET_SAVING_DUMMY', false));
  },

  deleteDummy({ commit }, dummy) {
    openConfirmDialog({
      title: i18n.t('confirm_dummy_delete'),
    }).then((confirmed) => {
      if (!confirmed) {
        return;
      }
      dummyService.delete(dummy).then(() => {
        commit('DELETE_DUMMY', dummy);
        eventBus.$emit(OPEN_SNACKBAR, i18n.t('dummy_deleted'));
      });
    });
  },

  clearDummyValidationErrors({ commit }, field) {
    commit('CLEAR_VALIDATION_ERRORS', field);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};
