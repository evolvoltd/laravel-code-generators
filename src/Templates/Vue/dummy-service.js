import http from './http';

const endpoint = 'api/dummys';

const dummyService = {
  model: 'dummy',

  getPage: (page, query) => http.get(`${endpoint}?page=${page}&${query}`),

  getAll: query => http.get(`${endpoint}?${query}`),

  search: query => http.get(`${endpoint}/find/${query}`),

  create: dummy => http.post(`${endpoint}`, dummy),

  update: dummy => http.put(`${endpoint}/${dummy.id}`, dummy),

  delete: dummy => http.delete(`${endpoint}/${dummy.id}`),
};

export default dummyService;
