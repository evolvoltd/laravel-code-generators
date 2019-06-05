import axios from 'axios';

const endpoint = 'api/dummys';

export const dummyService = {
  model: 'dummy',

  getPage: (page, query) => axios.get(`${endpoint}?page=${page}&${query}`),

  create: dummy => axios.post(`${endpoint}`, dummy),

  update: dummy => axios.put(`${endpoint}/${dummy.id}`, dummy),

  delete: dummy => axios.delete(`${endpoint}/${dummy.id}`),
};
