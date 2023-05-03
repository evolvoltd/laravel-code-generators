import httpClient from '@/api/http-client';

const endpoint = 'api/dummykcs';

const dummyService = {
  getPage: (params) => httpClient.get(endpoint, { params }),

  getById: (id) => httpClient.get(`${endpoint}/${id}`),

  search: (query) => httpClient.get(`${endpoint}/find/${query}`),

  create: (dummy) => httpClient.post(`${endpoint}`, dummy),

  update: (dummy) => httpClient.put(`${endpoint}/${dummy.id}`, dummy),

  delete: (dummy) => httpClient.delete(`${endpoint}/${dummy.id}`),
};

export default dummyService;
