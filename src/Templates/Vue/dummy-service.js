import httpClient from '@/api/http-client';

const endpoint = 'api/dummys';

const dummyService = {
  getPage: (params) => httpClient.get(endpoint, { params }),

  search: (query) => httpClient.get(`${endpoint}/find/${query}`),

  create: (dummy) => httpClient.post(`${endpoint}`, dummy),

  update: dummy => httpClient.put(`${endpoint}/${dummy.id}`, dummy),

  delete: dummy => httpClient.delete(`${endpoint}/${dummy.id}`),
};

export default dummyService;
