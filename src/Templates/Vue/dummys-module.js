import Vue from 'vue';
import dummyService from '@/api/dummykc-service';
import eventBus, { OPEN_SNACKBAR, openConfirmDialog } from '@/util/event-bus';
import i18n from '@/i18n/i18n-config';
import { updateArrayItem, removeArrayItem } from '@/util/array';
import { mapErrorsToInputs } from '@/util/forms';

export const getDefaultDummyFormItem = () => ({});

export const getDefaultDummyFilterParams = () => ({});

const state = {
  dummys: [],
  dummyPagination: {},
  editedDummy: {},
  newDummy: getDefaultDummyFormItem(),
  dummyValidationErrors: {},
  dummyFilterParams: getDefaultDummyFilterParams(),
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

  SET_DUMMY_FILTER_PARAMS(state, params) {
    state.dummyFilterParams = params;
  },

  SET_EDITED_DUMMY(state, dummy) {
    state.dummyValidationErrors = {};
    state.editedDummy = JSON.parse(JSON.stringify(dummy));
  },

  STORE_DUMMY(state, dummy) {
    state.dummys.push(dummy);
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

  CLEAR_DUMMY_VALIDATION_ERRORS(state, field) {
    Vue.delete(state.dummyValidationErrors, field);
  },
};

const actions = {
  fetchDummys({ commit }, params) {
    commit('SET_DUMMY_FILTER_PARAMS', params);
    return dummyService.getPage(params).then((res) => {
      commit('SET_DUMMYS', res.data);
      return res.data;
    });
  },

  storeDummy({ commit }, dummy) {
    return dummyService
      .create(dummy)
      .then((res) => {
        commit('STORE_DUMMY', res.data);
        eventBus.$emit(OPEN_SNACKBAR, i18n.t('dummysc_created'));
        return res.data;
      })
      .catch((err) => {
        commit('SET_DUMMY_VALIDATION_ERRORS', mapErrorsToInputs(err));
        throw err;
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
    return dummyService
      .update(dummy)
      .then((res) => {
        commit('UPDATE_DUMMY', res.data);
        eventBus.$emit(OPEN_SNACKBAR, i18n.t('dummysc_updated'));
        return res.data;
      })
      .catch((err) => {
        commit('SET_DUMMY_VALIDATION_ERRORS', mapErrorsToInputs(err));
        throw err;
      });
  },

  deleteDummy({ commit }, dummy) {
    openConfirmDialog({
      title: i18n.t('confirm_dummysc_delete'),
    }).then((confirmed) => {
      if (!confirmed) {
        return;
      }
      dummyService.delete(dummy).then(() => {
        commit('DELETE_DUMMY', dummy);
        eventBus.$emit(OPEN_SNACKBAR, i18n.t('dummysc_deleted'));
      });
    });
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};
